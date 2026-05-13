<?php

namespace App\Services\Checkout;

use App\Actions\Inventory\ReserveStockAction;
use App\Actions\Order\PlaceOrderAction;
use App\Models\CheckoutIdempotency;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheckoutOrchestratorService
{
    public function __construct(
        private readonly PlaceOrderAction $placeOrderAction,
        private readonly ReserveStockAction $reserveStockAction,
    ) {}

    /**
     * @param  array<string, mixed>  $cart
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $cart, array $validated, string $idempotencyKey): Order
    {
        if (trim($idempotencyKey) === '') {
            throw new RuntimeException('Missing idempotency key.');
        }

        $lockKey = $this->lockKey($idempotencyKey);

        return Cache::lock($lockKey, 10)->block(5, function () use ($cart, $validated, $idempotencyKey): Order {
            return DB::transaction(function () use ($cart, $validated, $idempotencyKey): Order {
                $requestHash = hash('sha256', json_encode([$cart, $validated], JSON_UNESCAPED_UNICODE));

                $record = CheckoutIdempotency::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->lockForUpdate()
                    ->first();

                if ($record && $record->status === 'completed' && $record->order_id) {
                    $existingOrder = Order::find($record->order_id);
                    if ($existingOrder) {
                        return $existingOrder;
                    }
                }

                if ($record && $record->request_hash && $record->request_hash !== $requestHash) {
                    throw new RuntimeException('Idempotency key reuse with different payload is not allowed.');
                }

                if (! $record) {
                    $record = CheckoutIdempotency::create([
                        'idempotency_key' => $idempotencyKey,
                        'status'          => 'processing',
                        'request_hash'    => $requestHash,
                        'expires_at'      => now()->addHours(24),
                    ]);
                }

                $order = $this->placeOrderAction->handle($cart, $validated);

                if (in_array($order->payment_method, ['midtrans', 'bank_transfer'], true)) {
                    $this->reserveStockAction->handle($order, $order->payment_method);
                }

                $record->update([
                    'order_id' => $order->id,
                    'status'   => 'completed',
                ]);

                return $order;
            }, 3);
        });
    }

    private function lockKey(string $idempotencyKey): string
    {
        $tenantId = tenant()?->getTenantKey() ?? 'central';
        return "checkout:idempotency:lock:{$tenantId}:{$idempotencyKey}";
    }
}

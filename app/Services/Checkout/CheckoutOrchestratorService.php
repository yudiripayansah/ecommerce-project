<?php

namespace App\Services\Checkout;

use App\Actions\Inventory\ReserveStockAction;
use App\Actions\Order\PlaceOrderAction;
use App\Models\CheckoutIdempotency;
use App\Models\CheckoutProcess;
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

                if ($record && $record->status === 'failed' && $record->order_id) {
                    $existingFailedOrder = Order::find($record->order_id);
                    if ($existingFailedOrder) {
                        return $existingFailedOrder;
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

                $process = CheckoutProcess::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->lockForUpdate()
                    ->first();

                if (! $process) {
                    $process = CheckoutProcess::create([
                        'idempotency_key'    => $idempotencyKey,
                        'state'              => 'initiated',
                        'context'            => ['payment_method' => $validated['payment_method'] ?? null],
                        'last_transition_at' => now(),
                    ]);
                }

                $order = $this->placeOrderAction->handle($cart, $validated);

                $process->update([
                    'order_id'            => $order->id,
                    'state'               => 'order_created',
                    'last_error_code'     => null,
                    'last_error_message'  => null,
                    'last_transition_at'  => now(),
                ]);

                if (in_array($order->payment_method, ['midtrans', 'bank_transfer'], true)) {
                    $this->reserveStockAction->handle($order, $order->payment_method);

                    $process->update([
                        'state'              => 'stock_reserved',
                        'last_transition_at' => now(),
                    ]);
                }

                $record->update([
                    'order_id'       => $order->id,
                    'status'         => 'completed',
                    'error_code'     => null,
                    'error_message'  => null,
                ]);

                $process->update([
                    'state'              => 'payment_initiated',
                    'last_transition_at' => now(),
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

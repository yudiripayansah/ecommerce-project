<?php

namespace App\Actions\Payment;

use App\Actions\Inventory\CancelStockReservationAction;
use App\Actions\Inventory\ReleaseStockReservationAction;
use App\Jobs\SendOrderNotificationsJob;
use App\Models\MidtransWebhookReceipt;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HandleMidtransWebhookAction
{
    public function __construct(
        private ReleaseStockReservationAction $releaseReservation,
        private CancelStockReservationAction  $cancelReservation,
    ) {}

    /**
     * Process a pre-validated Midtrans notification payload.
     * Signature validation is the caller's responsibility (done in MidtransController
     * before dispatching this action via ProcessMidtransWebhookJob).
     *
     * @param  array{transaction_status: string, fraud_status: string|null, order_id: string, payment_type: string, transaction_id: string}  $payload
     */
    public function handle(array $payload): void
    {
        $transactionStatus = $payload['transaction_status'];
        $fraudStatus       = $payload['fraud_status'] ?? null;
        $orderId           = $payload['order_id'];
        $paymentType       = $payload['payment_type'];
        $transactionId     = $payload['transaction_id'];

        $eventKey = $this->eventKey($orderId, $transactionId, $transactionStatus);

        $created = DB::transaction(function () use ($payload, $eventKey, $orderId, $transactionId, $transactionStatus): bool {
            $existing = MidtransWebhookReceipt::query()
                ->where('event_key', $eventKey)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return false;
            }

            MidtransWebhookReceipt::create([
                'event_key'           => $eventKey,
                'order_id'            => $orderId,
                'transaction_id'      => $transactionId,
                'transaction_status'  => $transactionStatus,
                'payload_hash'        => hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE)),
                'processed_at'        => now(),
            ]);

            return true;
        });

        if (! $created) {
            return;
        }

        $order = Order::with('items')->where('order_number', $orderId)->first();

        if (! $order) {
            Log::warning('Midtrans webhook: order not found', ['order_id' => $orderId]);
            return;
        }

        $order->update([
            'midtrans_transaction_id' => $transactionId,
            'midtrans_payment_type'   => $paymentType,
        ]);

        match (true) {
            $transactionStatus === 'capture' && $fraudStatus === 'accept' => $this->markPaid($order),
            $transactionStatus === 'settlement'                           => $this->markPaid($order),
            in_array($transactionStatus, ['cancel', 'deny', 'expire'], true) => $this->markCancelled($order),
            default                                                        => null,
        };
    }

    private function eventKey(string $orderId, string $transactionId, string $transactionStatus): string
    {
        $tenantId = tenant()?->getTenantKey() ?? 'central';
        return hash('sha256', implode('|', [$tenantId, $orderId, $transactionId, $transactionStatus]));
    }

    private function markPaid(Order $order): void
    {
        // Idempotent: only process once even if Midtrans sends duplicate webhooks
        if ($order->status === 'processing') {
            return;
        }

        $order->update(['status' => 'processing']);
        $this->releaseReservation->handle($order);

        // Dispatch notifications async — we're already inside a queued job,
        // but dispatching a separate job keeps the notification queue isolated.
        SendOrderNotificationsJob::dispatch($order->id, tenant()->getTenantKey())
            ->onQueue('notifications');
    }

    private function markCancelled(Order $order): void
    {
        $order->update(['status' => 'cancelled']);
        $this->cancelReservation->handle($order);
    }
}

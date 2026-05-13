<?php

namespace App\Jobs;

use App\Jobs\Concerns\RunsInTenantContext;
use App\Mail\Customer\OrderStatusUpdatedMail;
use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RunsInTenantContext;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int    $orderId,
        public readonly string $oldStatus,
        public readonly string $tenantId,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(WhatsAppService $whatsApp): void
    {
        $this->runForTenant(function () use ($whatsApp): void {
            $order = Order::find($this->orderId);

            if (! $order) {
                return;
            }

            try {
                Mail::to($order->customer_email)
                    ->send(new OrderStatusUpdatedMail($order, $this->oldStatus));
            } catch (\Throwable $e) {
                Log::warning('Order status email failed', [
                    'order' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }

            try {
                if ($order->status === 'shipped' && $order->tracking_number) {
                    $whatsApp->sendShipped($order);
                }
            } catch (\Throwable $e) {
                Log::warning('Order status WhatsApp failed', [
                    'order' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }
}

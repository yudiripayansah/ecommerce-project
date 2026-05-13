<?php

namespace App\Jobs;

use App\Actions\Order\SendOrderNotificationsAction;
use App\Jobs\Concerns\RunsInTenantContext;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RunsInTenantContext;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int    $orderId,
        public readonly string $tenantId,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(SendOrderNotificationsAction $action): void
    {
        $this->runForTenant(function () use ($action): void {
            $order = Order::with('items')->find($this->orderId);

            if (! $order) {
                return;
            }

            $action->handle($order);
        });
    }
}

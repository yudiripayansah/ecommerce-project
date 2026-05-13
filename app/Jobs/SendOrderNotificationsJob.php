<?php

namespace App\Jobs;

use App\Actions\Order\SendOrderNotificationsAction;
use App\Jobs\Concerns\AuditsTenantJob;
use App\Jobs\Concerns\RunsInTenantContext;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RunsInTenantContext, AuditsTenantJob;

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
        $startedAt = microtime(true);
        $audit = $this->auditJobStart([
            'order_id' => $this->orderId,
        ]);

        try {
            $this->runForTenant(function () use ($action): void {
                $order = Order::with('items')->find($this->orderId);

                if (! $order) {
                    return;
                }

                $action->handle($order);
            });

            $this->auditJobSuccess($audit, (int) ((microtime(true) - $startedAt) * 1000), [
                'order_id' => $this->orderId,
            ]);
        } catch (\Throwable $e) {
            $this->auditJobFailure($audit, (int) ((microtime(true) - $startedAt) * 1000), $e, [
                'order_id' => $this->orderId,
            ]);

            throw $e;
        }
    }
}

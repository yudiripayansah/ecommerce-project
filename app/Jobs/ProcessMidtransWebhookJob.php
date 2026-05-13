<?php

namespace App\Jobs;

use App\Actions\Payment\HandleMidtransWebhookAction;
use App\Jobs\Concerns\RunsInTenantContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMidtransWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RunsInTenantContext;

    /**
     * More retries + shorter backoff: Midtrans expects orders to be processed
     * relatively quickly, and the action is idempotent (order status check).
     */
    public int $tries   = 5;
    public int $backoff = 30;

    public function __construct(
        public readonly array  $payload,
        public readonly string $tenantId,
    ) {
        $this->onQueue('webhooks');
    }

    public function handle(HandleMidtransWebhookAction $webhook): void
    {
        $this->runForTenant(function () use ($webhook): void {
            $webhook->handle($this->payload);
        });
    }
}

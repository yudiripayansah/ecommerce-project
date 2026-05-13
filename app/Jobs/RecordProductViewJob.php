<?php

namespace App\Jobs;

use App\Jobs\Concerns\RunsInTenantContext;
use App\Models\ProductView;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordProductViewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RunsInTenantContext;

    // Analytics is not critical — one attempt only, no noise in failed jobs
    public int $tries = 1;

    public function __construct(
        public readonly int    $productId,
        public readonly string $sessionId,
        public readonly string $ipAddress,
        public readonly string $tenantId,
    ) {
        $this->onQueue('analytics');
    }

    public function handle(): void
    {
        $this->runForTenant(function (): void {
            ProductView::create([
                'product_id' => $this->productId,
                'session_id' => $this->sessionId,
                'ip_address' => $this->ipAddress,
                'created_at' => now(),
            ]);
        });
    }
}

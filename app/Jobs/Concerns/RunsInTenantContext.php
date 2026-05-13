<?php

namespace App\Jobs\Concerns;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

trait RunsInTenantContext
{
    /**
     * Initialize the tenant DB context, run the callback, then end tenancy.
     * Safe to call multiple times — tenancy()->end() is idempotent.
     */
    protected function runForTenant(callable $callback): void
    {
        $tenant = Tenant::find($this->tenantId);

        if (! $tenant) {
            Log::error('Queued job: tenant not found', [
                'tenant_id' => $this->tenantId,
                'job'       => static::class,
            ]);
            return;
        }

        tenancy()->initialize($tenant);

        try {
            $callback();
        } finally {
            tenancy()->end();
        }
    }
}

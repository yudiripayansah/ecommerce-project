<?php

namespace App\Jobs\Concerns;

use App\Models\QueueJobAudit;
use Throwable;

trait AuditsTenantJob
{
    protected function auditJobStart(array $context = []): QueueJobAudit
    {
        return QueueJobAudit::create([
            'job_class' => static::class,
            'queue'     => $this->queue ?? null,
            'tenant_id' => $this->tenantId ?? null,
            'status'    => 'started',
            'attempt'   => method_exists($this, 'attempts') ? $this->attempts() : 1,
            'context'   => $context ?: null,
        ]);
    }

    protected function auditJobSuccess(QueueJobAudit $audit, int $durationMs, array $context = []): void
    {
        $audit->update([
            'status'      => 'succeeded',
            'duration_ms' => $durationMs,
            'context'     => $context ?: $audit->context,
        ]);
    }

    protected function auditJobFailure(QueueJobAudit $audit, int $durationMs, Throwable $e, array $context = []): void
    {
        $audit->update([
            'status'        => 'failed',
            'duration_ms'   => $durationMs,
            'error_message' => mb_substr($e->getMessage(), 0, 65535),
            'context'       => $context ?: $audit->context,
        ]);
    }
}

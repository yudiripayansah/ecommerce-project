<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueJobAudit extends Model
{
    protected $fillable = [
        'job_class',
        'queue',
        'tenant_id',
        'status',
        'attempt',
        'duration_ms',
        'error_message',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MidtransWebhookReceipt extends Model
{
    protected $fillable = [
        'event_key',
        'order_id',
        'transaction_id',
        'transaction_status',
        'payload_hash',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];
}

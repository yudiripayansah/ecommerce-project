<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckoutProcess extends Model
{
    protected $fillable = [
        'idempotency_key',
        'order_id',
        'state',
        'context',
        'last_error_code',
        'last_error_message',
        'last_transition_at',
    ];

    protected $casts = [
        'context'            => 'array',
        'last_transition_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

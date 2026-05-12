<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'title',
        'variant_title',
        'price',
        'quantity',
        'image',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

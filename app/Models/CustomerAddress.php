<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
        'phone',
        'address',
        'city',
        'province',
        'postal_code',
        'country',
        'is_default',
        'province_id',
        'city_id',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function setAsDefault(): void
    {
        $this->customer->addresses()->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }
}

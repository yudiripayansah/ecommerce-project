<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
    // Immutable audit log — no updates allowed
    public $timestamps = false;

    protected $fillable = [
        'inventory_item_id',
        'quantity',
        'type',
        'reference_type',
        'reference_id',
        'note',
        'created_at',
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'created_at' => 'datetime',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->created_at ??= now());
    }
}

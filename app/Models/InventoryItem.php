<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'variant_id',
        'product_id',
        'warehouse_id',
        'quantity_reserved',
    ];

    protected $casts = [
        'quantity_reserved' => 'integer',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    /**
     * Shortcut to find (or create) the item row for a given variant or product.
     */
    public static function forVariant(int $variantId): self
    {
        return self::firstOrCreate(
            ['variant_id' => $variantId, 'product_id' => null, 'warehouse_id' => null],
            ['quantity_reserved' => 0]
        );
    }

    public static function forProduct(int $productId): self
    {
        return self::firstOrCreate(
            ['product_id' => $productId, 'variant_id' => null, 'warehouse_id' => null],
            ['quantity_reserved' => 0]
        );
    }
}

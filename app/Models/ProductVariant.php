<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'title',
        'price',
        'compare_at_price',
        'sku',
        'barcode',
        'inventory_quantity',
        'track_stock',
        'weight',
        'weight_unit',
        'option1',
        'option2',
        'option3',
        'position',
        'requires_shipping',
        'taxable',
    ];

    protected $casts = [
        'price'              => 'decimal:2',
        'compare_at_price'   => 'decimal:2',
        'weight'             => 'decimal:2',
        'requires_shipping'  => 'boolean',
        'taxable'            => 'boolean',
        'track_stock'        => 'boolean',
        'inventory_quantity' => 'integer',
    ];

    public function isInStock(int $qty = 1): bool
    {
        if (! $this->track_stock) {
            return true;
        }
        return $this->inventory_quantity >= $qty;
    }

    public function decrementStock(int $qty): void
    {
        if ($this->track_stock) {
            $this->decrement('inventory_quantity', $qty);
        }
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'handle',
        'description',
        'price',
        'compare_at_price',
        'vendor',
        'product_type',
        'status',
        'track_stock',
        'inventory_quantity',
        'featured_store_file_id',
        'option1_name',
        'option2_name',
        'option3_name',
        'published_at',
    ];

    protected $casts = [
        'price'              => 'decimal:2',
        'compare_at_price'   => 'decimal:2',
        'published_at'       => 'datetime',
        'track_stock'        => 'boolean',
        'inventory_quantity' => 'integer',
    ];

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(StoreFile::class, 'featured_store_file_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class)->withPivot('position');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('position');
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(StoreFile::class, 'product_store_file')
            ->withPivot('position')
            ->orderByPivot('position');
    }

    public function views(): HasMany
    {
        return $this->hasMany(ProductView::class);
    }
}

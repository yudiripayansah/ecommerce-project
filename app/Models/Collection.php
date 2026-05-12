<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StoreFile;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'handle',
        'description',
        'image',
        'store_file_id',
        'meta_title',
        'meta_description',
        'sort_order',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function storeFile(): BelongsTo
    {
        return $this->belongsTo(StoreFile::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('position')->orderByPivot('position');
    }
}

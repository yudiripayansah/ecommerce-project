<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Product;

class CollectionController extends Controller
{
    public function show(string $handle)
    {
        $sort    = request('sort', 'default');
        $perPage = 12;

        // Virtual "all" collection — shows every active product
        if ($handle === 'all') {
            $collection = new Collection([
                'title'       => 'All Products',
                'description' => null,
                'handle'      => 'all',
            ]);

            $query = Product::with('featuredImage')->where('status', 'active');

            $products = match ($sort) {
                'title-asc'  => $query->orderBy('title')->paginate($perPage),
                'title-desc' => $query->orderByDesc('title')->paginate($perPage),
                'price-asc'  => $query->orderBy('price')->paginate($perPage),
                'price-desc' => $query->orderByDesc('price')->paginate($perPage),
                default      => $query->latest()->paginate($perPage),
            };

            $products->appends(['sort' => $sort]);

            return view('theme.templates.collection', compact('collection', 'products', 'sort'));
        }

        $collection = Collection::with('storeFile')
            ->where('handle', $handle)
            ->whereNotNull('published_at')
            ->firstOrFail();

        $query = $collection->products()
            ->with('featuredImage')
            ->where('products.status', 'active');

        $products = match ($sort) {
            'title-asc'  => $query->orderBy('products.title')->paginate($perPage),
            'title-desc' => $query->orderByDesc('products.title')->paginate($perPage),
            'price-asc'  => $query->orderBy('products.price')->paginate($perPage),
            'price-desc' => $query->orderByDesc('products.price')->paginate($perPage),
            default      => $query->orderBy('collection_product.position')->paginate($perPage),
        };

        $products->appends(['sort' => $sort]);

        return view('theme.templates.collection', compact('collection', 'products', 'sort'));
    }
}

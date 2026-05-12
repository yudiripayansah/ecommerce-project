<?php

namespace App\Http\Controllers;

use App\Models\Product;

class SearchController extends Controller
{
    public function index()
    {
        $q       = trim(request('q', ''));
        $sort    = request('sort', 'default');
        $perPage = 12;

        $query = Product::with('featuredImage')->where('status', 'active');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('vendor', 'like', "%{$q}%")
                    ->orWhere('product_type', 'like', "%{$q}%");
            });
        }

        $products = match ($sort) {
            'title-asc'  => $query->orderBy('title')->paginate($perPage),
            'title-desc' => $query->orderByDesc('title')->paginate($perPage),
            'price-asc'  => $query->orderBy('price')->paginate($perPage),
            'price-desc' => $query->orderByDesc('price')->paginate($perPage),
            default      => $query->latest()->paginate($perPage),
        };

        $products->appends(['q' => $q, 'sort' => $sort]);

        return view('theme.templates.search', compact('products', 'q', 'sort'));
    }
}

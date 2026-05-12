<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductView;

class ProductController extends Controller
{
    public function show(string $handle)
    {
        $product = Product::with(['variants' => fn ($q) => $q->orderBy('position'), 'media', 'featuredImage'])
            ->where('handle', $handle)
            ->where('status', 'active')
            ->firstOrFail();

        ProductView::create([
            'product_id' => $product->id,
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);

        return view('theme.templates.product', compact('product'));
    }
}

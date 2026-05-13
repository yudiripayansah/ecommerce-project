<?php

namespace App\Http\Controllers;

use App\Jobs\RecordProductViewJob;
use App\Models\Product;

class ProductController extends Controller
{
    public function show(string $handle)
    {
        $product = Product::with(['variants' => fn ($q) => $q->orderBy('position'), 'media', 'featuredImage'])
            ->where('handle', $handle)
            ->where('status', 'active')
            ->firstOrFail();

        RecordProductViewJob::dispatch(
            $product->id,
            session()->getId(),
            request()->ip(),
            tenant()->getTenantKey()
        );

        return view('theme.templates.product', compact('product'));
    }
}

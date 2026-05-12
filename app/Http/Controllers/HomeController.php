<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $collections = Collection::with('storeFile')
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->limit(6)
            ->get();

        $products = Product::with('featuredImage')
            ->where('status', 'active')
            ->latest()
            ->limit(8)
            ->get();

        return view('theme.templates.index', compact('collections', 'products'));
    }
}

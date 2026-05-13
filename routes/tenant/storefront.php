<?php

declare(strict_types=1);

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ShippingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/products/{handle}', [ProductController::class, 'show'])->name('products.show');

Route::get('/collections/{handle}', [CollectionController::class, 'show'])->name('collections.show');

Route::get('/pages/{handle}', [PageController::class, 'show'])->name('pages.show');

Route::prefix('shipping')->name('shipping.')->group(function () {
    Route::get('/provinces', [ShippingController::class, 'provinces'])->name('provinces');
    Route::get('/cities',    [ShippingController::class, 'cities'])->name('cities');
    Route::post('/cost',     [ShippingController::class, 'cost'])->name('cost');
});

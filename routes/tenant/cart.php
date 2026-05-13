<?php

declare(strict_types=1);

use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::prefix('cart')->name('cart')->group(function () {
    Route::get('/',              [CartController::class, 'index']);
    Route::post('/add',          [CartController::class, 'add'])->name('.add');
    Route::post('/update/{key}', [CartController::class, 'update'])->name('.update');
    Route::post('/remove/{key}', [CartController::class, 'remove'])->name('.remove');
    Route::post('/clear',        [CartController::class, 'clear'])->name('.clear');
});

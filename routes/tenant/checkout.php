<?php

declare(strict_types=1);

use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::prefix('checkout')->name('checkout')->group(function () {
    Route::get('/',               [CheckoutController::class, 'index']);
    Route::post('/',              [CheckoutController::class, 'process'])->name('.process')->middleware('throttle:5,1');
    Route::get('/success',        [CheckoutController::class, 'success'])->name('.success');
    Route::post('/payment-proof', [CheckoutController::class, 'uploadProof'])->name('.payment-proof')->middleware('throttle:10,1');
});

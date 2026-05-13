<?php

declare(strict_types=1);

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Account\AuthController;
use App\Http\Controllers\Account\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('account')->name('account.')->group(function () {

    // ── Guest only ────────────────────────────────────────────────────────────
    Route::middleware('customer.guest')->group(function () {
        Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login',    [AuthController::class, 'login'])->middleware('throttle:5,1');
        Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ── Authenticated customers ───────────────────────────────────────────────
    Route::middleware('customer.auth')->group(function () {

        Route::get('/',         [AccountController::class, 'index'])->name('index');
        Route::put('/profile',  [AccountController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [AccountController::class, 'updatePassword'])->name('password.update');

        Route::prefix('orders')->name('orders')->group(function () {
            Route::get('/',                       [OrderController::class, 'index']);
            Route::get('/{order}',                [OrderController::class, 'show'])->name('.show');
            Route::post('/{order}/payment-proof', [OrderController::class, 'uploadProof'])->name('.proof');
        });

        Route::prefix('addresses')->name('addresses')->group(function () {
            Route::get('/',                  [AddressController::class, 'index']);
            Route::post('/',                 [AddressController::class, 'store'])->name('.store');
            Route::put('/{address}',         [AddressController::class, 'update'])->name('.update');
            Route::delete('/{address}',      [AddressController::class, 'destroy'])->name('.destroy');
            Route::put('/{address}/default', [AddressController::class, 'setDefault'])->name('.default');
        });
    });
});

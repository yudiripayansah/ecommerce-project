<?php

declare(strict_types=1);

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Account\AuthController;
use App\Http\Controllers\Account\OrderController;
use App\Http\Controllers\Admin\ProductExportController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImportTemplateController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ShippingController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckTenantActive;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    CheckTenantActive::class,
])->group(function () {

    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/search', [SearchController::class, 'index'])->name('search');

    Route::get('/products/{handle}', [ProductController::class, 'show'])->name('products.show');

    Route::get('/collections/{handle}', [CollectionController::class, 'show'])->name('collections.show');

    Route::get('/pages/{handle}', [PageController::class, 'show'])->name('pages.show');

    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update/{key}', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove/{key}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::post('/checkout/payment-proof', [CheckoutController::class, 'uploadProof'])->name('checkout.payment-proof');

    // ── Shipping / RajaOngkir ──────────────────────────────────────────────
    Route::prefix('shipping')->name('shipping.')->group(function () {
        Route::get('/provinces', [ShippingController::class, 'provinces'])->name('provinces');
        Route::get('/cities',    [ShippingController::class, 'cities'])->name('cities');
        Route::post('/cost',     [ShippingController::class, 'cost'])->name('cost');
    });

    // ── Midtrans ───────────────────────────────────────────────────────────
    Route::post('/midtrans/notification', [MidtransController::class, 'notification'])
        ->name('midtrans.notification');

    // ── Admin Tools ────────────────────────────────────────────────────────
    Route::middleware('auth')->prefix('admin-tools')->group(function () {
        Route::get('/products/import-template', [ProductImportTemplateController::class, 'download'])
            ->name('admin.products.import-template');
        Route::get('/products/export-excel', [ProductExportController::class, 'excel'])
            ->name('admin.products.export-excel');
        Route::get('/products/export-pdf', [ProductExportController::class, 'pdf'])
            ->name('admin.products.export-pdf');
    });

    // ── Customer Account ───────────────────────────────────────────────────
    Route::prefix('account')->name('account.')->group(function () {

        Route::middleware('customer.guest')->group(function () {
            Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
            Route::post('/login',   [AuthController::class, 'login']);
            Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
            Route::post('/register', [AuthController::class, 'register']);
        });

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::middleware('customer.auth')->group(function () {
            Route::get('/',            [AccountController::class, 'index'])->name('index');
            Route::put('/profile',     [AccountController::class, 'updateProfile'])->name('profile.update');
            Route::put('/password',    [AccountController::class, 'updatePassword'])->name('password.update');

            Route::get('/orders',                          [OrderController::class, 'index'])->name('orders');
            Route::get('/orders/{order}',                  [OrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/{order}/payment-proof',   [OrderController::class, 'uploadProof'])->name('orders.proof');

            Route::get('/addresses',                           [AddressController::class, 'index'])->name('addresses');
            Route::post('/addresses',                          [AddressController::class, 'store'])->name('addresses.store');
            Route::put('/addresses/{address}',                 [AddressController::class, 'update'])->name('addresses.update');
            Route::delete('/addresses/{address}',              [AddressController::class, 'destroy'])->name('addresses.destroy');
            Route::put('/addresses/{address}/default',         [AddressController::class, 'setDefault'])->name('addresses.default');
        });
    });
});

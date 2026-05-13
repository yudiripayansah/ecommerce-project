<?php

declare(strict_types=1);

use App\Http\Middleware\CheckTenantActive;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    CheckTenantActive::class,
])->group(function () {
    require __DIR__ . '/tenant/storefront.php';
    require __DIR__ . '/tenant/cart.php';
    require __DIR__ . '/tenant/checkout.php';
    require __DIR__ . '/tenant/account.php';
    require __DIR__ . '/tenant/webhook.php';
    require __DIR__ . '/tenant/admin.php';
});

<?php

use Illuminate\Support\Facades\Route;

// ── Central domain routes (SaaS landing / super-admin) ─────────────────────
// The store frontend and tenant admin panel are served via subdomain routes
// defined in routes/tenant.php (loaded by TenancyServiceProvider).

Route::get('/', function () {
    return view('welcome');
})->name('home');

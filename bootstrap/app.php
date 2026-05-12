<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'customer.auth'  => \App\Http\Middleware\CustomerAuthenticated::class,
            'customer.guest' => \App\Http\Middleware\CustomerGuest::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'midtrans/notification',
        ]);

        // Initialize tenant context for ALL web requests (including Livewire updates).
        // Gracefully skips central domains and unknown domains.
        $middleware->prependToGroup('web', \App\Http\Middleware\InitializeTenancyByDomainIfTenant::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\ApplyStoreTheme::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

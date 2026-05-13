<?php

declare(strict_types=1);

use App\Http\Controllers\MidtransController;
use Illuminate\Support\Facades\Route;

// CSRF excluded for this path via bootstrap/app.php → validateCsrfTokens(except: ['midtrans/notification'])
// Signature validated synchronously inside MidtransController before dispatching the job.
Route::post('/midtrans/notification', [MidtransController::class, 'notification'])
    ->name('midtrans.notification')
    ->middleware('throttle:120,1');

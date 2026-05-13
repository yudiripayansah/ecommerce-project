<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ProductExportController;
use App\Http\Controllers\ProductImportTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('admin-tools')->name('admin.')->group(function () {

    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/import-template', [ProductImportTemplateController::class, 'download'])->name('import-template');
        Route::get('/export-excel',    [ProductExportController::class, 'excel'])->name('export-excel');
        Route::get('/export-pdf',      [ProductExportController::class, 'pdf'])->name('export-pdf');
    });
});

<?php

use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProductExportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/queue-run', function () {

    Artisan::call('optimize:clear');

    return 'Queue started';
});

Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/export/products', [ProductExportController::class, 'export'])->name('products.export');
    Route::get('/guarantee/{id}', [PdfController::class, 'guaranteePdf'])->name('pdf.guarantee');
});

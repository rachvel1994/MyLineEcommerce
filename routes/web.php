<?php

use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProductExportController;
use App\Http\Controllers\WidgetReportExportController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/queue-run', function () {

    Artisan::call('optimize:clear');

    return 'Queue started';
});

Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/export/products', [ProductExportController::class, 'export'])->name('products.export');
    Route::get('/export/widget-reports', WidgetReportExportController::class)->name('widget-reports.export');
    Route::get('/guarantee/{id}', [PdfController::class, 'guaranteePdf'])->name('pdf.guarantee');
});

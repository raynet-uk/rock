<?php

use Modules\Afteractionreport\Http\Controllers\AfterActionReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/afteractionreport')
    ->name('admin.afteractionreport.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/',          [AfterActionReportController::class, 'index'])  ->name('index');
        Route::get('/create',    [AfterActionReportController::class, 'create']) ->name('create');
        Route::post('/',         [AfterActionReportController::class, 'store'])  ->name('store');
        Route::get('/{id}',      [AfterActionReportController::class, 'show'])   ->name('show');
        Route::get('/{id}/edit', [AfterActionReportController::class, 'edit'])   ->name('edit');
        Route::put('/{id}',      [AfterActionReportController::class, 'update']) ->name('update');
        Route::delete('/{id}',   [AfterActionReportController::class, 'destroy'])->name('destroy');
    });

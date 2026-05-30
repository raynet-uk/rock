<?php

use App\Http\Controllers\Admin\ModuleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module Manager Routes
|--------------------------------------------------------------------------
| Include this file inside your admin prefix + middleware group in web.php:
|
|   Route::prefix('admin')->middleware(['web', 'admin'])->group(function () {
|       require __DIR__ . '/modules.php';
|   });
|
*/

Route::prefix('modules')->name('admin.modules.')->group(function () {

    Route::get   ('/',                    [ModuleController::class, 'index'])         ->name('index');
    Route::post  ('/{alias}/enable',      [ModuleController::class, 'enable'])        ->name('enable');
    Route::post  ('/{alias}/disable',     [ModuleController::class, 'disable'])       ->name('disable');
    Route::post  ('/{alias}/update',      [ModuleController::class, 'update'])        ->name('update');
    Route::post  ('/{alias}/delete',      [ModuleController::class, 'delete'])        ->name('delete');
    Route::post  ('/refresh-updates',     [ModuleController::class, 'refreshUpdates'])->name('refresh-updates');
    Route::post  ('/upload',              [ModuleController::class, 'upload'])        ->name('upload');
    Route::get ('/browse',              [ModuleController::class, 'browseRegistry'])    ->name('browse');
    Route::post('/install-from-registry',[ModuleController::class, 'installFromRegistry'])->name('install-from-registry');
    Route::get('updates/check',         [ModuleController::class, 'checkUpdates'])->name('modules.updates.check');
    Route::post('{alias}/update/apply', [ModuleController::class, 'applyUpdate']) ->name('modules.update.apply');

});

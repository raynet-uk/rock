<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlertStatusApiController;
use App\Http\Controllers\Api\CommandCentreController;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::match(['get','post'], '/alerts/level', [AlertStatusApiController::class, 'handle'])
    ->name('api.alert-status.handle');

Route::get('/report', [App\Http\Controllers\ReportApiController::class, 'index']);

// ── Command Centre remote control ─────────────────────────────────────────
Route::prefix('command-centre')->group(function () {
    Route::post('reset-password',      [CommandCentreController::class, 'resetPassword']);
    Route::post('set-alert',           [CommandCentreController::class, 'setAlert']);
    Route::post('set-setting',         [CommandCentreController::class, 'setSetting']);
    Route::post('get-setting',         [CommandCentreController::class, 'getSetting']);
    Route::get ('get-settings',        [CommandCentreController::class, 'getSettings']);
    Route::get ('activity-logs',       [CommandCentreController::class, 'getActivityLogs']);
    Route::post('activity-logs/add',   [CommandCentreController::class, 'addActivityLog']);
    Route::post('activity-logs/update',[CommandCentreController::class, 'updateActivityLog']);
    Route::post('activity-logs/delete',[CommandCentreController::class, 'deleteActivityLog']);
    Route::get ('members',             [CommandCentreController::class, 'getMembers']);
    Route::get ('member-profile',      [CommandCentreController::class, 'memberProfile']); // 👈 add here
    Route::post('update-member-profile', [CommandCentreController::class, 'updateMemberProfile']);
    Route::post('send-notification',       [CommandCentreController::class, 'sendNotification']);
    Route::get('notification-status',      [CommandCentreController::class, 'notificationStatus']);
    Route::post('delete-notification',     [CommandCentreController::class, 'deleteNotification']);
});

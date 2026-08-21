<?php

use App\Http\Controllers\DeviceTokenController;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return 'hello world';
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/device-token', [DeviceTokenController::class, 'store']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});

// auth routes
require __DIR__.'/api/auth.php';

// patient routes
require __DIR__.'/api/patients.php';

// admin routes
require __DIR__.'/api/admin.php';

// doctor routes
require __DIR__.'/api/doctors.php';

// reception
require __DIR__.'/api/receptionist.php';

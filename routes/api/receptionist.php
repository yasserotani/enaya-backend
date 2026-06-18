<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Receptionist\AppointmentController;
use App\Http\Controllers\Receptionist\PatientController;
use Illuminate\Support\Facades\Route;

// Receptionist patient routes
Route::middleware(['auth:sanctum', 'role:receptionist'])
    ->prefix('reception/patients')
    ->group(function () {
        Route::get('/', [PatientController::class, 'index']);
        Route::post('/', [PatientController::class, 'store']);
        Route::get('/{patient}', [PatientController::class, 'show']);
        Route::put('/{patient}', [PatientController::class, 'update']);
        Route::delete('/{patient}', [PatientController::class, 'destroy']);
        Route::put('/{patient}/restore', [PatientController::class, 'restore']); // Added this route
        Route::delete('/{patient}/force-delete', [PatientController::class, 'forceDelete']); // Added this route
    });


// Receptionist appointment management (view, update, cancel, reschedule, mark arrived/no-show)
Route::middleware(['auth:sanctum', 'role:receptionist'])->prefix('appointments/reception')->group(function () {
    Route::get('/', [AppointmentController::class, 'index']);
    Route::post('/', [AppointmentController::class, 'store']);
    Route::get('/{appointment}', [AppointmentController::class, 'show']);
    Route::patch('/{appointment}/confirm', [AppointmentController::class, 'confirm']);
    Route::patch('/{appointment}/arrived', [AppointmentController::class, 'markArrived']);
    Route::patch('/{appointment}/reschedule', [AppointmentController::class, 'reschedule']);
    Route::patch('/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    Route::patch('/{appointment}/no-show', [AppointmentController::class, 'markNoShow']);
});

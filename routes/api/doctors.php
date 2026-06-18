<?php

use App\Http\Controllers\Doctor\PatientController;
use App\Http\Controllers\Doctor\PrescriptionController;
use App\Http\Controllers\Doctor\AppointmentSessionController;
use App\Http\Controllers\Doctor\AppointmentController;

// Add this line
use Illuminate\Support\Facades\Route;

Route::prefix('doctor')->middleware(['auth:sanctum', 'role:doctor'])->group(function () {

    Route::prefix('appointments')->group(function () { // Changed to 'appointments' for the main resource
        Route::get('/', [AppointmentController::class, 'index']); // Route for index
        Route::get('/{appointment}', [AppointmentController::class, 'show']); // Route for show
        Route::patch('/{appointment}/cancel', [AppointmentController::class, 'cancel']); // Route for cancel
        Route::patch('/{appointment}/no-show', [AppointmentController::class, 'markNoShow']); // Route for markNoShow

        Route::prefix('{appointment}')->group(function () { // Nested group for session-related routes
            Route::get('sessions/list', [AppointmentSessionController::class, 'index']);
            Route::post('sessions/end', [AppointmentSessionController::class, 'end']);
            Route::post('sessions/start', [AppointmentSessionController::class, 'start']);
            Route::get('sessions/{session}', [AppointmentSessionController::class, 'show']);
            Route::patch('sessions/{session}', [AppointmentSessionController::class, 'update']);
        });
    });

    Route::prefix('{doctor}')->group(function () {
        Route::get('/patients', [PatientController::class, 'index']);
        Route::get('/patients/{patient}', [PatientController::class, 'show']);
    });

    Route::prefix('sessions/{session}')->group(function () {
        Route::post('/prescriptions', [PrescriptionController::class, 'store']);
        Route::delete('/prescriptions/{prescription}', [PrescriptionController::class, 'destroy']);
    });
});

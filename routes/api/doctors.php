<?php

use App\Http\Controllers\Doctor\AppointmentController;
use App\Http\Controllers\Doctor\AppointmentSessionController;
use App\Http\Controllers\Doctor\PatientController;
use App\Http\Controllers\Doctor\PrescriptionController;
// Add this line
use Illuminate\Support\Facades\Route;

Route::prefix('doctor')->middleware(['auth:sanctum', 'role:doctor'])->group(function () {

    Route::prefix('appointments')->group(function () {
        Route::get('/', [AppointmentController::class, 'index']);
        Route::get('/{appointment}', [AppointmentController::class, 'show']);
        Route::patch('/{appointment}/cancel', [AppointmentController::class, 'cancel']);
        Route::patch('/{appointment}/no-show', [AppointmentController::class, 'markNoShow']);
        Route::patch('/{appointment}/confirm', [AppointmentController::class, 'confirm']);
        Route::get('/available-slots', [AppointmentController::class, 'availableSlots']); // Moved here
        Route::get('/available-days', [AppointmentController::class, 'availableDays']); // Moved here

        Route::prefix('{appointment}')->group(function () {
            Route::get('sessions/list', [AppointmentSessionController::class, 'index']);
            Route::post('sessions/end', [AppointmentSessionController::class, 'end']);
            Route::post('sessions/start', [AppointmentSessionController::class, 'start']);
            Route::get('sessions/{session}', [AppointmentSessionController::class, 'show']);
            Route::patch('sessions/{session}', [AppointmentSessionController::class, 'update']);
        });
    });

    Route::get('/patients', [PatientController::class, 'index']);
    Route::get('/patients/{patient}', [PatientController::class, 'show']);

    Route::prefix('sessions/{session}')->group(function () {
        Route::post('/prescriptions', [PrescriptionController::class, 'store']);
        Route::delete('/prescriptions/{prescription}', [PrescriptionController::class, 'destroy']);
        Route::put('/prescriptions/{prescription}', [PrescriptionController::class, 'update']);
    });
});

<?php

use App\Http\Controllers\Doctor\DoctorController;
use App\Http\Controllers\Doctor\PrescriptionController;
use App\Http\Controllers\Doctor\SessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('doctor')->middleware(['auth:sanctum', 'role:doctor'])->group(function () {

    Route::prefix('appointments/{appointment}')->group(function () {
        // ... your existing appointment routes

        Route::get('sessions/list', [SessionController::class, 'index']);
        Route::post('sessions/end', [SessionController::class, 'end']);
        Route::post('sessions/start', [SessionController::class, 'start']);
        Route::get('sessions/{session}', [SessionController::class, 'show']);
        Route::patch('sessions/{session}', [SessionController::class, 'update']);
    });

    // Prescriptions (separate, session-scoped)
    Route::post('sessions/{session}/prescriptions', [PrescriptionController::class, 'store']);
    Route::delete('sessions/{session}/prescriptions/{prescription}', [PrescriptionController::class, 'destroy']);
});

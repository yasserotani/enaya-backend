<?php

use App\Http\Controllers\Doctor\PatientController;
use App\Http\Controllers\Doctor\PrescriptionController;
use App\Http\Controllers\Doctor\SessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('doctor')->middleware(['auth:sanctum', 'role:doctor'])->group(function () {

    Route::prefix('appointments/{appointment}')->group(function () {

        Route::get('sessions/list', [SessionController::class, 'index']);
        Route::post('sessions/end', [SessionController::class, 'end']);
        Route::post('sessions/start', [SessionController::class, 'start']);
        Route::get('sessions/{session}', [SessionController::class, 'show']);
        Route::patch('sessions/{session}', [SessionController::class, 'update']);
    });
    Route::prefix('patients/{patient}')->group(function () {
        Route::get('/doctors/{doctor}/patients', [PatientController::class, 'index']);
        Route::get('/doctors/{doctor}/patients/{patient}', [PatientController::class, 'show']);
    });

    Route::prefix('sessions/{session}')->group(function () {
        Route::post('/prescriptions', [PrescriptionController::class, 'store']);
        Route::delete('/prescriptions/{prescription}', [PrescriptionController::class, 'destroy']);
    });
});

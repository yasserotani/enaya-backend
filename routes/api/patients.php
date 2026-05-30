<?php

use App\Http\Controllers\Patient\PatientController;
use Illuminate\Support\Facades\Route;

// patient  reception routes
Route::prefix('patients')->group(function () {
    Route::post('/store_reception_patient', [PatientController::class, 'store_reception_patient']);
    Route::post('/update_reception_patient/{id}', [PatientController::class, 'update_reception_patient']);
    // patient application
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/patient_profile', [PatientController::class, 'show']);
        Route::post('/complete_profile', [PatientController::class, 'store']);
        Route::put('/patient_update', [PatientController::class, 'update']);
    });
});

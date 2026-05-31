<?php

use App\Http\Controllers\Patient\ProfileController;
use App\Http\Controllers\Patient\ReceptionPatientController;
use Illuminate\Support\Facades\Route;

// patient reception routes
Route::middleware(['auth:sanctum', 'role:receptionist'])
    ->prefix('reception')
    ->group(function () {
        Route::get('/', [ReceptionPatientController::class, 'index']);
        Route::post('/', [ReceptionPatientController::class, 'store']);
        Route::get('/{patient}', [ReceptionPatientController::class, 'show']);
        Route::put('/{patient}', [ReceptionPatientController::class, 'update']);
        Route::delete('/{patient}', [ReceptionPatientController::class, 'destroy']);
    });


// patient profile routes (authenticated)
Route::middleware(['auth:sanctum', 'role:patient'])
    ->prefix('patients')
    ->group(function () {
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::post('/complete-profile', [ProfileController::class, 'complete']);
        Route::put('/profile', [ProfileController::class, 'update']);
    });

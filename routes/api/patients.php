<?php

use App\Http\Controllers\Appointment\AppointmentController;
use App\Http\Controllers\Doctor\PatientController as DoctorPatientController;
use App\Http\Controllers\Patient\ProfileController;
use App\Http\Controllers\Receptionist\ReceptionPatientController;
use Illuminate\Support\Facades\Route;

// Receptionist patient routes
Route::middleware(['auth:sanctum', 'role:receptionist'])
    ->prefix('patients/reception')
    ->group(function () {
        Route::get('/', [ReceptionPatientController::class, 'index']);
        Route::post('/', [ReceptionPatientController::class, 'store']);
        Route::get('/{patient}', [ReceptionPatientController::class, 'show']);
        Route::put('/{patient}', [ReceptionPatientController::class, 'update']);
        Route::delete('/{patient}', [ReceptionPatientController::class, 'destroy']);
    });

// Doctor Patient routes
Route::middleware(['auth:sanctum', 'permission:view-patients'])
    ->get('/doctors/{doctor}/patients', [DoctorPatientController::class, 'index']);

// Patient profile routes
Route::middleware(['auth:sanctum', 'role:patient'])
    ->prefix('patients')
    ->group(function () {
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::post('/complete-profile', [ProfileController::class, 'complete']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/appointments', [AppointmentController::class, 'store']);
    });

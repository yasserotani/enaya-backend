<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Patient\AppointmentController;
use App\Http\Controllers\Patient\ProfileController;
use App\Http\Controllers\Patient\PrescriptionController;
use App\Http\Controllers\Patient\AppointmentSessionController;
use App\Http\Controllers\Patient\DoctorController; // Import the new DoctorController

// Profil
Route::middleware(['auth:sanctum', 'role:patient'])
    ->prefix('patients')
    ->group(function () {
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::post('/complete-profile', [ProfileController::class, 'complete']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::get('/department-doctors', [ProfileController::class, 'getDepartmentDoctors']);
    });

// Appointment
Route::middleware(['auth:sanctum', 'role:patient'])
    ->prefix('appointments/patient')
    ->group(function () {
        Route::get('/', [AppointmentController::class, 'index']);
        Route::post('/', [AppointmentController::class, 'store']);
        Route::get('/available-slots', [AppointmentController::class, 'availableSlots']);
        Route::patch('/{appointment}/cancel', [AppointmentController::class, 'cancel']);
        Route::patch('/{appointment}/reschedule', [AppointmentController::class, 'reschedule']);
        Route::get('/{appointment}', [AppointmentController::class, 'show']); // Added show route
    });

// Prescriptions
Route::middleware(['auth:sanctum', 'role:patient'])
    ->prefix('prescriptions/patient')
    ->group(function () {
        Route::get('/', [PrescriptionController::class, 'index']);
        Route::get('/{prescription}', [PrescriptionController::class, 'show']);
    });

// Appointment Sessions
Route::middleware(['auth:sanctum', 'role:patient'])
    ->prefix('sessions/patient')
    ->group(function () {
        Route::get('/', [AppointmentSessionController::class, 'index']);
        Route::get('/{session}', [AppointmentSessionController::class, 'show']);
    });

// Doctors
Route::middleware(['auth:sanctum', 'role:patient'])
    ->prefix('doctors')
    ->group(function () {
        Route::get('/', [DoctorController::class, 'index']);
        Route::get('/{doctor}', [DoctorController::class, 'show']);
    });

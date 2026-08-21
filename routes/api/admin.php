<?php

use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->group(function () {

        // ashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // users
        Route::apiResource('users', UserController::class);
        Route::patch('users/{user}/activate', [UserController::class, 'activate']); // Added activate route
        Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate']); // Added deactivate route

        // patients
        Route::apiResource('patients', PatientController::class);
        Route::put('patients/{patient}/restore', [PatientController::class, 'restore']);
        Route::delete('patients/{patient}/force-delete', [PatientController::class, 'forceDelete']);

        // doctors
        Route::apiResource('doctors', DoctorController::class)->withTrashed(['show']);
        Route::put('doctors/{doctor}/restore', [DoctorController::class, 'restore']); // Fixed route
        Route::patch('doctors/{doctor}/reset-password', [DoctorController::class, 'resetPassword']); // Fixed route

        // departments
        Route::apiResource('departments', DepartmentController::class);
        Route::get('departments/{department}/doctors', [DepartmentController::class, 'getDepartmentDoctors']);

        // appointment
        Route::get('appointments', [AppointmentController::class, 'index']);
        Route::get('appointments/medical-record/{patient}', [AppointmentController::class, 'medicalRecord']);
        Route::get('appointments/stats', [AppointmentController::class, 'stats']);
        Route::get('appointments/available-slots', [AppointmentController::class, 'availableSlots']);
        Route::get('/available-days', [AppointmentController::class, 'availableDays']);
        Route::post('appointments', [AppointmentController::class, 'store']);

        Route::get('appointments/{appointment}', [AppointmentController::class, 'show']);
        Route::patch('appointments/{appointment}/confirm', [AppointmentController::class, 'confirm']);
        Route::patch('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
        Route::patch('appointments/{appointment}/no-show', [AppointmentController::class, 'markNoShow']);
        Route::patch('appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule']);
        Route::patch('appointments/{appointment}/arrived', [AppointmentController::class, 'markArrived']);
    });

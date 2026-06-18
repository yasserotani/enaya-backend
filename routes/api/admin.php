<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{
    AppointmentController,
    DashboardController,
    DepartmentController,
    DoctorController,
    PatientController,
    UserController
};

Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::apiResource('users', UserController::class);

        Route::apiResource('patients', PatientController::class);
        Route::put('patients/{patient}/restore', [PatientController::class, 'restore']);
//      Route::delete('patients/{patient}/force-delete', [PatientController::class, 'forceDelete']);

        Route::apiResource('doctors', DoctorController::class);
        Route::put('doctors/{doctor}/restore', [DoctorController::class, 'restore']);
        Route::patch('doctors/{doctor}/reset-password', [DoctorController::class, 'resetPassword']);

        Route::apiResource('departments', DepartmentController::class);


        Route::get('appointments', [AppointmentController::class, 'index']);
        Route::get('appointments/stats', [AppointmentController::class, 'stats']);
        Route::get('appointments/{appointment}', [AppointmentController::class, 'show']);
        Route::patch('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
        Route::patch('appointments/{appointment}/no-show', [AppointmentController::class, 'markNoShow']);
        Route::patch('appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule']);
    });

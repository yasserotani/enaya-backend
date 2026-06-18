<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\PatientController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->group(function () {

        Route::apiResource('users', UserController::class);
        Route::apiResource('patients', PatientController::class);
        Route::apiResource('doctors', DoctorController::class);
        Route::apiResource('departments', DepartmentController::class)->middleware('permission:manage-departments');
        Route::put('doctors/{doctor}/restore', [DoctorController::class, 'restore']);

        Route::get('/dashboard', [DashboardController::class, 'index']);
    });

<?php

use App\Http\Controllers\Doctor\PatientController as DoctorPatientController;
use App\Http\Controllers\Patient\ProfileController;
use App\Http\Controllers\Patient\ReceptionPatientController;
use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\Appointment\AppointmentController;
// patient reception routes
Route::middleware(['auth:sanctum', 'role:receptionist'])
    ->prefix('patients/reception')
    ->group(function () {
        Route::get('/', [ReceptionPatientController::class, 'index'])->name('patients.reception.index');
        Route::post('/', [ReceptionPatientController::class, 'store'])->name('patients.reception.store');
        Route::get('/{patient}', [ReceptionPatientController::class, 'show'])->name('patients.reception.show');
        Route::put('/{patient}', [ReceptionPatientController::class, 'update'])->name('patients.reception.update');
        Route::delete('/{patient}', [ReceptionPatientController::class, 'destroy'])->name('patients.reception.destroy');
    });

// list patients for a given doctor (doctors and staff with view permission)
Route::middleware(['auth:sanctum', 'permission:view-patients'])
    ->get('/doctors/{doctor}/patients', [DoctorPatientController::class, 'index'])
    ->name('doctors.patients.index');

// patient profile routes (authenticated)
Route::middleware(['auth:sanctum', 'role:patient'])
    ->prefix('patients')
    ->group(function () {
        Route::get('/profile', [ProfileController::class, 'show'])->name('patients.profile.show');
        Route::post('/complete-profile', [ProfileController::class, 'complete'])->name('patients.profile.complete');
        Route::put('/profile', [ProfileController::class, 'update'])->name('patients.profile.update');
        Route::post('/appointments', [AppointmentController::class, 'store']);
        });

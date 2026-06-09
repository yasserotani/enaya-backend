<?php

use App\Http\Controllers\Appointment\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:patient'])->prefix('appointments')->group(function () {
    Route::get('/my_all_appointments', [AppointmentController::class, 'index']); // All my appointments
    Route::post('/book_appointment', [AppointmentController::class, 'store']); // Book a new appointment
    Route::get('/doctor', [AppointmentController::class, 'getMyAppointmentsWithDoctor']); // My appointments with a specific doctor
    Route::get('/available-slots', [AppointmentController::class, 'getDoctorAvailableSlots']); // Get doctor's available slots
});

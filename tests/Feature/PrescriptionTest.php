<?php

use App\Models\Appointment;
use App\Models\AppointmentSession;
use App\Models\Prescription;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a prescription associated with an appointment session', function () {
    $session = AppointmentSession::factory()->create();

    $prescription = Prescription::factory()->create([
        'appointment_session_id' => $session->id,
        'medication_name' => 'Amoxicillin',
    ]);

    expect($prescription->appointment_session_id)->toBe($session->id);
    expect($prescription->appointmentSession->id)->toBe($session->id);
});

it('can retrieve prescriptions from an appointment session', function () {
    $session = AppointmentSession::factory()->create();

    $prescription = Prescription::factory()->create([
        'appointment_session_id' => $session->id,
    ]);

    expect($session->prescriptions)->toHaveCount(1);
    expect($session->prescriptions->first()->id)->toBe($prescription->id);
});

it('can retrieve prescriptions from an appointment through has-many-through relationship', function () {
    $appointment = Appointment::factory()->create();
    $session = AppointmentSession::factory()->create([
        'appointment_id' => $appointment->id,
    ]);

    $prescription = Prescription::factory()->create([
        'appointment_session_id' => $session->id,
    ]);

    // Retrieve via HasManyThrough relationship on Appointment
    expect($appointment->prescriptions)->toHaveCount(1);
    expect($appointment->prescriptions->first()->id)->toBe($prescription->id);
});

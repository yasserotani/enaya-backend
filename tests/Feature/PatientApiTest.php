<?php

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Permission::firstOrCreate(['name' => 'edit-app-patients', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'delete-app-patients', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'view-patients', 'guard_name' => 'web']);

    Role::firstOrCreate(['name' => 'receptionist', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'patient', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web'])
        ->givePermissionTo('view-patients');
});

test('receptionist can create a walk-in patient', function (): void {
    $receptionist = User::factory()->create();
    $receptionist->assignRole('receptionist');

    $payload = [
        'full_name' => 'Jane Doe',
        'phone' => '+963912345678',
        'date_of_birth' => '1995-05-20',
        'gender' => 'female',
        'address' => 'Damascus',
        'job' => 'Teacher',
    ];

    $this->actingAs($receptionist)
        ->postJson('/api/patients/reception', $payload)
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.full_name', 'Jane Doe')
        ->assertJsonPath('data.profile_completed', true);

    $this->assertDatabaseHas('patients', [
        'full_name' => 'Jane Doe',
        'phone' => '+963912345678',
        'user_id' => null,
        'profile_completed' => true,
    ]);
});

test('receptionist cannot create duplicate phone patients', function (): void {
    $receptionist = User::factory()->create();
    $receptionist->assignRole('receptionist');

    Patient::factory()->create([
        'phone' => '+963912345678',
        'user_id' => null,
    ]);

    $this->actingAs($receptionist)
        ->postJson('/api/patients/reception', [
            'full_name' => 'Another Patient',
            'phone' => '+963912345678',
            'gender' => 'female',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['phone']);
});

test('receptionist can update a walk-in patient', function (): void {
    $receptionist = User::factory()->create();
    $receptionist->assignRole('receptionist');

    $patient = Patient::factory()->create([
        'user_id' => null,
        'phone' => '+963912345678',
        'full_name' => 'Jane Doe',
    ]);

    $this->actingAs($receptionist)
        ->putJson('/api/patients/reception/'.$patient->id, [
            'full_name' => 'Jane Smith',
            'phone' => '+963998877665',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.full_name', 'Jane Smith');

    $this->assertDatabaseHas('patients', [
        'id' => $patient->id,
        'full_name' => 'Jane Smith',
        'phone' => '+963998877665',
    ]);
});

test('receptionist cannot update an app patient without permission', function (): void {
    $receptionist = User::factory()->create();
    $receptionist->assignRole('receptionist');

    $patientUser = User::factory()->create();
    $patientUser->assignRole('patient');
    $patient = Patient::factory()->create([
        'user_id' => $patientUser->id,
    ]);

    $this->actingAs($receptionist)
        ->putJson('/api/patients/reception/'.$patient->id, [
            'full_name' => 'Blocked Update',
        ])
        ->assertStatus(403)
        ->assertJsonPath('success', false);
});

test('receptionist can delete a walk-in patient', function (): void {
    $receptionist = User::factory()->create();
    $receptionist->assignRole('receptionist');

    $patient = Patient::factory()->create([
        'user_id' => null,
    ]);

    $this->actingAs($receptionist)
        ->deleteJson('/api/patients/reception/'.$patient->id)
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseMissing('patients', [
        'id' => $patient->id,
    ]);
});

test('patient can view and update their profile', function (): void {
    $user = User::factory()->create([
        'name' => 'Patient User',
        'email' => 'patient@example.com',
    ]);
    $user->assignRole('patient');

    Patient::factory()->create([
        'user_id' => $user->id,
        'full_name' => 'Patient User',
        'phone' => '+963912345678',
        'profile_completed' => false,
    ]);

    $this->actingAs($user)
        ->getJson('/api/patients/profile')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Patient User');

    $this->actingAs($user)
        ->putJson('/api/patients/profile', [
            'name' => 'Updated Patient',
            'email' => 'updated@example.com',
            'phone' => '+963933333333',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'address' => 'Homs',
            'job' => 'Engineer',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Updated Patient')
        ->assertJsonPath('data.phone', '+963933333333');
});

test('patient can complete their profile once', function (): void {
    $user = User::factory()->create();
    $user->assignRole('patient');

    Patient::factory()->create([
        'user_id' => $user->id,
        'profile_completed' => false,
    ]);

    $this->actingAs($user)
        ->postJson('/api/patients/complete-profile', [
            'full_name' => 'Completed Patient',
            'phone' => '+963944444444',
            'date_of_birth' => '1992-02-02',
            'gender' => 'female',
            'address' => 'Aleppo',
            'job' => 'Designer',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.profile_completed', true);
});

test('doctor can list their patients', function (): void {
    $doctorUser = User::factory()->create();
    $doctorUser->assignRole('doctor');

    $doctor = Doctor::factory()->create([
        'user_id' => $doctorUser->id,
    ]);

    // patients with appointments for this doctor
    $p1 = Patient::factory()->create(['user_id' => null]);
    $p2 = Patient::factory()->create(['user_id' => null]);

    Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'patient_id' => $p1->id,
    ]);

    Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'patient_id' => $p2->id,
    ]);

    // unrelated patient
    Patient::factory()->create();

    $this->actingAs($doctorUser)
        ->getJson('/api/doctors/'.$doctor->id.'/patients')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'data');
});

test('doctor can start a session for an arrived appointment', function (): void {
    $doctorUser = User::factory()->create();
    $doctorUser->assignRole('doctor');

    $doctor = Doctor::factory()->create([
        'user_id' => $doctorUser->id,
    ]);

    $patient = Patient::factory()->create(['user_id' => null]);

    $appointment = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'status' => AppointmentStatus::Arrived->value,
    ]);

    $this->actingAs($doctorUser, 'sanctum')
        ->postJson('/api/doctor/appointments/'.$appointment->id.'/sessions/start', [
            'patient_complaint' => 'Persistent headache',
            'notes' => 'Needs physician evaluation.',
        ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.session.status', 'in_progress');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => AppointmentStatus::InProgress->value,
    ]);
});

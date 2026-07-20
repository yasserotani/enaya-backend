<?php

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns doctor working hours by id', function () {
    $user = User::factory()->create();
    $doctor = Doctor::factory()->create([
        'user_id' => $user->id,
        'working_hours_start' => '08:00',
        'working_hours_end' => '14:00',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson(route('doctors.show', ['doctor' => $doctor->id]));

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'data' => [
            'id' => $doctor->id,
            'userId' => $user->id,
            'name' => $user->name,
            'workingHours' => [
                'start' => '08:00',
                'end' => '14:00',
            ],
        ],
    ]);
});

it('returns doctors by name and working hours', function () {
    $user = User::factory()->create(['name' => 'Dr. Samir']);
    $doctor = Doctor::factory()->create([
        'user_id' => $user->id,
        'working_hours_start' => '08:00',
        'working_hours_end' => '14:00',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson(route('doctors.by-name', ['name' => 'Samir']));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonFragment([
        'id' => $doctor->id,
        'workingHours' => [
            'start' => '08:00',
            'end' => '14:00',
        ],
    ]);
});

it('updates doctor working hours successfully', function () {
    $user = User::factory()->create();
    $doctor = Doctor::factory()->create([
        'user_id' => $user->id,
        'working_hours_start' => '08:00',
        'working_hours_end' => '14:00',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson(route('doctors.update', ['doctor' => $doctor->id]), [
        'working_hours_start' => '09:00',
        'working_hours_end' => '16:00',
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'data' => [
            'id' => $doctor->id,
            'workingHours' => [
                'start' => '09:00',
                'end' => '16:00',
            ],
        ],
    ]);
});

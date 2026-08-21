<?php

use App\Models\Doctor;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('keeps the requested calendar day when building available slots', function () {
    Carbon::setTestNow(Carbon::create(2026, 8, 21, 10, 0, 0, config('app.timezone')));

    $doctor = Doctor::factory()->create([
        'working_hours_start' => '08:00:00',
        'working_hours_end' => '12:00:00',
    ]);

    $slots = app(AppointmentService::class)->availableSlots(
        $doctor->id,
        Carbon::parse('2026-08-21T00:00:00+14:00')
    );

    expect($slots)->toBe([
        '2026-08-21 10:30:00',
        '2026-08-21 11:00:00',
        '2026-08-21 11:30:00',
    ]);
});

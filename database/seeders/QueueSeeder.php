<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Queue;
use Illuminate\Database\Seeder;

class QueueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = Doctor::query()->with('appointments')->get();
        $patients = Patient::query()->get();

        if ($doctors->isEmpty() || $patients->isEmpty()) {
            return;
        }

        foreach ($doctors as $doctor) {
            $queueCount = fake()->numberBetween(2, 3);
            $availableAppointments = $doctor->appointments->values();

            for ($position = 1; $position <= $queueCount; $position++) {
                $appointment = $position === 1 ? $availableAppointments->first() : null;

                Queue::create([
                    'doctor_id' => $doctor->id,
                    'patient_id' => $patients->random()->id,
                    'appointment_id' => $appointment?->id,
                    'position' => $position,
                    'status' => fake()->randomElement(['waiting', 'in_session', 'done']),
                    'waiting_since' => now()->subMinutes(fake()->numberBetween(10, 180)),
                    'notes' => fake()->optional(0.5)->sentence(),
                ]);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = Doctor::query()->get();
        $patients = Patient::query()->get();

        if ($doctors->isEmpty() || $patients->isEmpty()) {
            return;
        }

        $appointmentCount = fake()->numberBetween(10, 20);
        $statuses = array_column(AppointmentStatus::cases(), 'value');

        for ($index = 0; $index < $appointmentCount; $index++) {
            Appointment::create([
                'doctor_id' => $doctors->random()->id,
                'patient_id' => $patients->random()->id,
                'scheduled_at' => now()->addDays($index + 1)->setTime(fake()->numberBetween(8, 16), fake()->randomElement([0, 30])),
                'status' => fake()->randomElement($statuses),
                'notes' => fake()->optional(0.7)->sentence(),
            ]);
        }
    }
}

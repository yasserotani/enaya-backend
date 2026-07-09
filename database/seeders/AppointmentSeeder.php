<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Appointment::query()->exists()) {
            return;
        }

        $doctors = Doctor::all();
        $patients = Patient::all();

        if ($doctors->isEmpty() || $patients->isEmpty()) {
            return;
        }

        $appointmentCount = fake()->numberBetween(100, 300); // Increased count for more data
        $statuses = array_column(AppointmentStatus::cases(), 'value');

        // Prevent duplicate (doctor_id + scheduled_at)
        $usedSlots = [];

        for ($i = 0; $i < $appointmentCount; $i++) {

            $doctorId = $doctors->random()->id;

            // Generate unique slot for this doctor, including today and future dates
            do {
                $date = fake()->dateTimeBetween('-1 month', '+3 months'); // Mix of past, present, and future
                $date = Carbon::parse($date)->setTime(
                    fake()->numberBetween(8, 16), // Working hours 8 AM to 4 PM
                    fake()->randomElement([0, 30]) // 0 or 30 minutes
                );

                $slotKey = $doctorId . '_' . $date->format('Y-m-d H:i:s');

            } while (isset($usedSlots[$slotKey]));

            // Mark slot as used
            $usedSlots[$slotKey] = true;

            Appointment::create([
                'doctor_id' => $doctorId,
                'patient_id' => $patients->random()->id,
                'scheduled_at' => $date,
                'status' => fake()->randomElement($statuses),
                'visit_reason' => fake()->optional(0.7)->sentence(),
                'notes' => fake()->optional(0.7)->sentence(),
            ]);
        }
    }
}

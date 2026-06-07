<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Prescription;
use Illuminate\Database\Seeder;

class PrescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appointments = Appointment::query()->get();

        if ($appointments->isEmpty()) {
            return;
        }

        $appointmentCount = (int) ($appointments->count() * 0.6);

        if ($appointmentCount === 0) {
            return;
        }

        foreach ($appointments->random($appointmentCount) as $appointment) {
            Prescription::factory()->for($appointment)->create();
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\AppointmentSession;
use App\Models\Prescription;
use Illuminate\Database\Seeder;

class PrescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sessions = AppointmentSession::query()->get();

        if ($sessions->isEmpty()) {
            return;
        }

        $sessionCount = (int) ($sessions->count() * 0.6);

        if ($sessionCount === 0) {
            return;
        }

        foreach ($sessions->random($sessionCount) as $session) {
            Prescription::factory()->for($session)->create();
        }
    }
}

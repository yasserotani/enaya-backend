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

        $sessionCount = (int) ($sessions->count() * 0.8);

        if ($sessionCount === 0) {
            return;
        }

        $rows = [];
        foreach ($sessions->random($sessionCount) as $session) {
            $data = Prescription::factory()->make()->toArray();
            $data['appointment_session_id'] = $session->id;
            $data['created_at'] = now();
            $data['updated_at'] = now();
            $rows[] = $data;
        }

        if (! empty($rows)) {
            foreach (array_chunk($rows, 200) as $chunk) {
                \Illuminate\Support\Facades\DB::table((new Prescription)->getTable())->insert($chunk);
            }
        }
    }
}

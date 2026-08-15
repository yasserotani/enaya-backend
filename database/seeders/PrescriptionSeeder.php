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
        // Temporary query counter for diagnostics
        $queryCount = 0;
        \Illuminate\Support\Facades\DB::listen(function ($query) use (&$queryCount) {
            $queryCount++;
        });

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
            $data = Prescription::factory()->make(['appointment_session_id' => $session->id])->toArray();
            $data['created_at'] = now();
            $data['updated_at'] = now();
            $rows[] = $data;
        }

        if (! empty($rows)) {
            foreach (array_chunk($rows, 200) as $chunk) {
                \Illuminate\Support\Facades\DB::table((new Prescription)->getTable())->insert($chunk);
            }
        }

        // Output diagnostics: query count
        if (isset($this->command) && $this->command) {
            $this->command->info('PrescriptionSeeder query count: ' . $queryCount);
        } else {
            \Illuminate\Support\Facades\Log::info('PrescriptionSeeder query count: ' . $queryCount);
        }
    }
}

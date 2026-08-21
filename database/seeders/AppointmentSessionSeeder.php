<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentSession;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AppointmentSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Schema::hasTable('appointment_sessions')) {
            return;
        }

        // Clear old sessions before creating new ones based on fresh appointments
        Schema::withoutForeignKeyConstraints(function () {
            AppointmentSession::truncate();
        });

        // Temporary query counter for diagnostics
        $queryCount = 0;
        DB::listen(function ($query) use (&$queryCount) {
            $queryCount++;
        });

        $appointments = Appointment::query()
            ->whereDoesntHave('sessions')
            ->get();

        if ($appointments->isEmpty()) {
            return;
        }

        $rows = [];
        $now = now();
        foreach ($appointments as $appointment) {
            $appointmentStatus = $appointment->status instanceof AppointmentStatus
                ? $appointment->status->value
                : (string) $appointment->status;

            // Align session times with the appointment's scheduled_at so sessions fall on the same day
            $apptDate = Carbon::parse($appointment->scheduled_at);
            $isOldAppointment = $apptDate->isPast();

            if ($isOldAppointment) {
                $sessionStatus = match ($appointmentStatus) {
                    AppointmentStatus::Canceled->value => 'cancelled',
                    AppointmentStatus::Completed->value => 'completed',
                    default => fake()->randomElement(['completed', 'cancelled']),
                };
            } else {
                $sessionStatus = match ($appointmentStatus) {
                    AppointmentStatus::InProgress->value => 'in_progress',
                    AppointmentStatus::Canceled->value => 'cancelled',
                    AppointmentStatus::Completed->value => 'completed',
                    default => 'pending',
                };
            }

            $startedAt = null;
            $endedAt = null;

            switch ($sessionStatus) {
                case 'completed':
                    // session started around scheduled time and lasted some minutes
                    $startedAt = $apptDate->copy()->addMinutes(fake()->numberBetween(-10, 5));
                    $endedAt = $startedAt->copy()->addMinutes(fake()->numberBetween(10, 60));
                    break;
                case 'in_progress':
                    // session likely started at or slightly before scheduled time
                    $startedAt = $apptDate->copy()->addMinutes(fake()->numberBetween(-5, 0));
                    $endedAt = null;
                    break;
                case 'pending':
                    // no started/ended times
                    $startedAt = null;
                    $endedAt = null;
                    break;
                case 'cancelled':
                    // cancelled sessions have no session activity details
                    $startedAt = null;
                    $endedAt = null;
                    break;
            }

            $row = AppointmentSession::factory()->make([
                'appointment_id' => $appointment->id,
                'status' => $sessionStatus,
            ])->toArray();

            // Ensure MySQL-compatible DATETIME strings (Y-m-d H:i:s) — factory may produce ISO8601 strings
            $row['started_at'] = $startedAt ? Carbon::parse($startedAt)->format('Y-m-d H:i:s') : null;
            $row['ended_at'] = $endedAt ? Carbon::parse($endedAt)->format('Y-m-d H:i:s') : null;

            $row['created_at'] = $now;
            $row['updated_at'] = $now;

            $rows[] = $row;
        }

        // Bulk insert appointment sessions
        if (! empty($rows)) {
            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table((new AppointmentSession)->getTable())->insert($chunk);
            }
        }

        // Output diagnostics: query count
        if (isset($this->command) && $this->command) {
            $this->command->info('AppointmentSessionSeeder query count: '.$queryCount);
        } else {
            Log::info('AppointmentSessionSeeder query count: '.$queryCount);
        }
    }
}

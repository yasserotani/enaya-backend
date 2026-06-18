<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AppointmentSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Schema::hasTable('appointment_sessions')) {
            return;
        }

        $appointments = Appointment::query()->get();

        if ($appointments->isEmpty()) {
            return;
        }

        foreach ($appointments as $appointment) {
            if ($appointment->sessions()->exists()) {
                continue;
            }

            $appointmentStatus = $appointment->status instanceof AppointmentStatus
                ? $appointment->status->value
                : (string)$appointment->status;

            $sessionStatus = match ($appointmentStatus) {
                AppointmentStatus::Completed->value => 'completed',
                AppointmentStatus::Canceled->value => 'cancelled',
                default => 'in_progress',
            };

            AppointmentSession::factory()
                ->for($appointment)
                ->create([
                    'status' => $sessionStatus,
                ]);
        }
    }
}

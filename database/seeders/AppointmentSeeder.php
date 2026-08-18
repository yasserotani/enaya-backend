<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentSession;
use App\Models\Doctor;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        Schema::withoutForeignKeyConstraints(function () {
            AppointmentSession::truncate();
            Appointment::truncate();
        });

        $startDate = Carbon::today();
        $endDate = $startDate->copy()->addDays(10); // inclusive: today .. today+10

        $rows = [];
        $createdAt = now();
        $updatedAt = now();
        $appointmentsPerDoctorPerDay = 5;

        for ($day = $startDate->copy(); $day->lte($endDate); $day->addDay()) {
            foreach ($doctors as $doctor) {
                $startTime = Carbon::parse($doctor->working_hours_start)->format('H:i');
                $endTime = Carbon::parse($doctor->working_hours_end)->format('H:i');

                $slotCursor = $day->copy()->setTimeFromTimeString($startTime);
                $slotEnd = $day->copy()->setTimeFromTimeString($endTime);
                $slotCandidates = [];

                while ($slotCursor->lessThan($slotEnd)) {
                    $slotCandidates[] = $slotCursor->copy();
                    $slotCursor->addMinutes(30);
                }

                if (count($slotCandidates) < $appointmentsPerDoctorPerDay) {
                    continue;
                }

                $chosenSlots = collect($slotCandidates)
                    ->shuffle()
                    ->take($appointmentsPerDoctorPerDay);

                foreach ($chosenSlots as $scheduledAt) {
                    $patient = $patients->random();

                    $rows[] = [
                        'doctor_id' => $doctor->id,
                        'patient_id' => $patient->id,
                        'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
                        'status' => fake()->randomElement([
                            AppointmentStatus::Scheduled->value,
                            AppointmentStatus::Confirmed->value,
                            AppointmentStatus::Arrived->value,
                            AppointmentStatus::InProgress->value,
                        ]),
                        'visit_reason' => fake()->optional(0.7)->sentence(),
                        'notes' => fake()->optional(0.7)->sentence(),
                        'created_at' => $createdAt,
                        'updated_at' => $updatedAt,
                    ];
                }
            }
        }

        if (! empty($rows)) {
            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table((new Appointment)->getTable())->insert($chunk);
            }
        }

        if (isset($this->command) && $this->command) {
            $this->command->info('AppointmentSeeder inserted '.count($rows).' appointments from '.$startDate->toDateString().' to '.$endDate->toDateString().'.');
        }
    }
}

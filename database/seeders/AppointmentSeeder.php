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

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Always load fresh doctors and patients from database
        $doctors = Doctor::all();
        $patients = Patient::all();

        if ($doctors->isEmpty() || $patients->isEmpty()) {
            return;
        }

        // Delete old appointments created by DoctorSeeder so we can recreate with proper patient distribution
        // First disable foreign key checks to allow deletion
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        AppointmentSession::truncate();
        Appointment::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $appointmentCount = 1000; // Increased count for more data
        $statuses = array_column(AppointmentStatus::cases(), 'value');

        // Prevent duplicate (doctor_id + scheduled_at)
        $usedSlots = [];

        // Generate appointments for the next 7 days so each day has in-progress appointments
        $daysToSeed = [];
        $start = Carbon::today();
        for ($d = 0; $d < 7; $d++) {
            $daysToSeed[] = $start->copy()->addDays($d);
        }

        // For each seeded day, create an 'inProgress' appointment for every 30-minute slot
        // during each doctor's working hours so there is an appointment in progress at every time.
        foreach ($daysToSeed as $day) {
            foreach ($doctors as $doctor) {
                // cursor from doctor's working_hours_start to working_hours_end
                $cursor = Carbon::parse($day)->setTimeFromTimeString($doctor->working_hours_start);
                $end = Carbon::parse($day)->setTimeFromTimeString($doctor->working_hours_end);

                while ($cursor->lessThan($end)) {
                    $slotKey = $doctor->id.'_'.$cursor->format('Y-m-d H:i:s');

                    if (! isset($usedSlots[$slotKey])) {
                        $usedSlots[$slotKey] = true;

                        // Use a random patient from all available patients in database
                        $patient = $patients->random();

                        Appointment::create([
                            'doctor_id' => $doctor->id,
                            'patient_id' => $patient->id,
                            'scheduled_at' => $cursor->copy(),
                            'status' => AppointmentStatus::InProgress->value,
                            'visit_reason' => fake()->optional(0.7)->sentence(),
                            'notes' => fake()->optional(0.7)->sentence(),
                        ]);
                    }

                    $cursor->addMinutes(30);
                }
            }
        }

        // Fill remaining appointments with a wider date range if total count is not met
        $existingAppointmentsCount = Appointment::count();
        $remainingAppointments = $appointmentCount - $existingAppointmentsCount;

        // Reload fresh patient list for remaining appointments
        $patientsList = Patient::all();

        for ($i = 0; $i < $remainingAppointments; $i++) {
            $doctorId = $doctors->random()->id;

            // Generate unique slot for this doctor, including today and future dates
            do {
                $date = fake()->dateTimeBetween('-1 month', '+1 months'); // Mix of past, present, and future
                $date = Carbon::parse($date)->setTime(
                    fake()->numberBetween(8, 16), // Working hours 8 AM to 4 PM
                    fake()->randomElement([0, 30]) // 0 or 30 minutes
                );

                $slotKey = $doctorId.'_'.$date->format('Y-m-d H:i:s');

            } while (isset($usedSlots[$slotKey]));

            // Mark slot as used
            $usedSlots[$slotKey] = true;

            // Determine status with higher probability for 'InProgress'
            $status = fake()->randomElement([
                AppointmentStatus::InProgress->value, // Higher chance for in progress
                AppointmentStatus::InProgress->value,
                AppointmentStatus::Scheduled->value,
                AppointmentStatus::Confirmed->value,
                AppointmentStatus::Completed->value,
                AppointmentStatus::Canceled->value,
                AppointmentStatus::NoShow->value,
            ]);

            // Use random patient from all available patients in database
            $patient = $patientsList->random();

            Appointment::create([
                'doctor_id' => $doctorId,
                'patient_id' => $patient->id,
                'scheduled_at' => $date,
                'status' => $status,
                'visit_reason' => fake()->optional(0.7)->sentence(),
                'notes' => fake()->optional(0.7)->sentence(),
            ]);
        }
    }
}

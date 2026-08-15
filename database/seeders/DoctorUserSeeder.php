<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DoctorUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $department = Department::query()->first();

        if (! $department) {
            return;
        }

        $doctorUser = User::firstOrCreate(
            ['email' => 'doctor@enaya.com'],
            [
                'name' => 'Doctor',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $doctorUser->assignRole('doctor');

        $doctor = Doctor::firstOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'department_id' => $department->id,
                'specialty' => 'General Practice',
                'full_name' => $doctorUser->name,
                'phone' => '1234567893',
                'date_of_birth' => fake()->dateTimeBetween('-65 years', '-20 years'),
                'gender' => 'male',
                'working_hours_start' => '08:00:00',
                'working_hours_end' => '16:00:00',
            ]
        );

        // Attach recurring appointments: 7 slots per day from now for the next 14 days
        $patients = Patient::query()->pluck('id')->toArray();
        if (! empty($patients)) {
            // Allow split seeding via SEED_DAY or SEED_START/SEED_END env vars
            $seedDay = env('SEED_DAY');
            $seedStart = env('SEED_START');
            $seedEnd = env('SEED_END');

            if ($seedDay) {
                $start = Carbon::parse($seedDay)->startOfDay();
                $end = Carbon::parse($seedDay)->endOfDay();
            } elseif ($seedStart || $seedEnd) {
                $start = $seedStart ? Carbon::parse($seedStart)->startOfDay() : Carbon::now()->startOfDay();
                $end = $seedEnd ? Carbon::parse($seedEnd)->endOfDay() : Carbon::now()->addDays(14)->endOfDay();
            } else {
                $start = Carbon::now()->startOfDay();
                $end = Carbon::now()->addDays(14)->endOfDay();
            }

            // Load existing scheduled slots for this doctor in the range to avoid duplicates
            $existing = Appointment::where('doctor_id', $doctor->id)
                ->whereBetween('scheduled_at', [$start->toDateTimeString(), $end->toDateTimeString()])
                ->pluck('scheduled_at')
                ->map(function ($dt) {
                    return Carbon::parse($dt)->format('Y-m-d H:i:s');
                })
                ->toArray();
            $existing = array_flip($existing); // for faster isset checks

            $rows = [];
            $hours = [9, 10, 11, 12, 13, 14, 15]; // 7 slots per day
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                foreach ($hours as $hour) {
                    $scheduledAt = $d->copy()->setTime($hour, 0, 0);
                    $key = $scheduledAt->format('Y-m-d H:i:s');
                    if (isset($existing[$key])) {
                        continue;
                    }

                    $patientId = $patients[array_rand($patients)];

                    $rows[] = [
                        'doctor_id' => $doctor->id,
                        'patient_id' => $patientId,
                        'scheduled_at' => $key,
                        'status' => AppointmentStatus::Scheduled->value,
                        'visit_reason' => null,
                        'notes' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (! empty($rows)) {
                foreach (array_chunk($rows, 200) as $chunk) {
                    DB::table((new Appointment)->getTable())->insert($chunk);
                }
                if (isset($this->command) && $this->command) {
                    $this->command->info('DoctorUserSeeder inserted '.count($rows).' appointments for doctor@enaya.com');
                }
            }
        }
    }
}

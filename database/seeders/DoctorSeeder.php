<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentSession;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = Department::query()->pluck('id');
        $allPatients = Patient::all();

        // Global array to track used slots for all doctors
        $usedSlots = [];

        // Temporary query counter for diagnostics
        $queryCount = 0;
        DB::listen(function ($query) use (&$queryCount) {
            $queryCount++;
        });

        // Bulk-create users for doctors
        $seedPrefix = 'bulk-doctor-' . now()->timestamp . '-';
        $userRows = [];
        for ($i = 1; $i <= 25; $i++) {
            $name = fake()->name();
            $email = $seedPrefix . $i . '@example.test';
            $userRows[] = [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table((new User)->getTable())->insert($userRows);

        // Fetch created users
        $createdUsers = DB::table((new User)->getTable())->where('email', 'like', $seedPrefix . '%')->get();
        $createdUserIds = $createdUsers->pluck('id')->toArray();

        // Bulk-create doctors linked to the created users
        $doctorRows = [];
        foreach ($createdUsers as $cu) {
            $doctorRows[] = [
                'user_id' => $cu->id,
                'department_id' => $departments->random(),
                'specialty' => 'General Practice',
                'full_name' => $cu->name,
            // Use unique phone per doctor to avoid unique constraint violations
            'phone' => fake()->unique()->numerify('9#########'),
            'date_of_birth' => \Carbon\Carbon::parse(fake()->dateTimeBetween('-65 years', '-20 years'))->format('Y-m-d'),
            'gender' => 'male',
            'working_hours_start' => '08:00:00',
            'working_hours_end' => '16:00:00',
            'created_at' => now(),
            'updated_at' => now(),
            ];
        }
        DB::table((new Doctor)->getTable())->insert($doctorRows);

        // Assign 'doctor' role via bulk pivot insert
        $roleId = DB::table(config('permission.table_names.roles'))->where('name', 'doctor')->value('id');
        if ($roleId) {
            $pivotRows = [];
            foreach ($createdUserIds as $uid) {
                $pivotRows[] = [
                    'role_id' => $roleId,
                    'model_id' => $uid,
                    'model_type' => User::class,
                ];
            }
            DB::table(config('permission.table_names.model_has_roles'))->insert($pivotRows);
        }

        // Build appointment rows for the newly created doctors
        $doctors = Doctor::whereIn('user_id', $createdUserIds)->get();
        $appointmentRows = [];
        foreach ($doctors as $doctor) {
            $numAppointments = rand(5, 15);
            $createdAt = now();
            for ($i = 0; $i < $numAppointments; $i++) {
                $patient = $allPatients->random();

                $doctorId = $doctor->id;

                // Generate scheduled_at for this appointment
                $scheduledAt = Carbon::parse(fake()->dateTimeBetween('-1 month', '+3 months'))->setTime(
                    fake()->numberBetween(8, 17),
                    fake()->randomElement([0, 30])
                );

                $slotKey = $doctorId.'_'.$scheduledAt->format('Y-m-d H:i:s');

                if (isset($usedSlots[$slotKey])) {
                    // regenerate until unique
                    do {
                        $scheduledAt = Carbon::parse(fake()->dateTimeBetween('-1 month', '+3 months'))->setTime(
                            fake()->numberBetween(8, 17),
                            fake()->randomElement([0, 30])
                        );
                        $slotKey = $doctorId.'_'.$scheduledAt->format('Y-m-d H:i:s');
                    } while (isset($usedSlots[$slotKey]));
                }

                // Mark slot as used
                $usedSlots[$slotKey] = true;

                $status = fake()->randomElement(array_column(AppointmentStatus::cases(), 'value'));

                $appointmentRows[] = [
                    'doctor_id' => $doctorId,
                    'patient_id' => $patient->id,
                    'scheduled_at' => $scheduledAt,
                    'status' => $status,
                    'visit_reason' => fake()->optional(0.7)->sentence(),
                    'notes' => fake()->optional(0.7)->text(100),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }
        }

        // Bulk insert doctor-created appointments
        if (! empty($appointmentRows)) {
            foreach (array_chunk($appointmentRows, 200) as $chunk) {
                DB::table((new Appointment)->getTable())->insert($chunk);
            }
        }

        // Output diagnostics: query count
        if (isset($this->command) && $this->command) {
            $this->command->info('DoctorSeeder query count: ' . $queryCount);
        } else {
            \Illuminate\Support\Facades\Log::info('DoctorSeeder query count: ' . $queryCount);
        }
    }
}

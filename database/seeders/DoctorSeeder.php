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

        $appointmentRows = [];
        $users = User::factory()->count(25)->create();
        foreach ($users as $user) {
            $user->assignRole('doctor');

            $doctor = Doctor::factory()->for($user)->create([
                'department_id' => $departments->random(),
            ]);

            $numAppointments = rand(5, 15);
            $createdAt = now();
            $made = Appointment::factory()->count($numAppointments)->make()->toArray();
            for ($i = 0; $i < $numAppointments; $i++) {
                $patient = $allPatients->random();

                $doctorId = $doctor->id;

                // Use scheduled_at from factory if present, otherwise generate
                $scheduledAt = isset($made[$i]['scheduled_at']) ? Carbon::parse($made[$i]['scheduled_at']) : Carbon::parse(fake()->dateTimeBetween('-1 month', '+3 months'))->setTime(
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
                    'visit_reason' => $made[$i]['visit_reason'] ?? null,
                    'notes' => $made[$i]['notes'] ?? null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];
            }
        }

        // Bulk insert doctor-created appointments
        if (! empty($appointmentRows)) {
            foreach (array_chunk($appointmentRows, 200) as $chunk) {
                \Illuminate\Support\Facades\DB::table((new Appointment)->getTable())->insert($chunk);
            }
        }
    }
}

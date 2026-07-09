<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentSession;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

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

        User::factory()
            ->count(10)
            ->create()
            ->each(function (User $user) use ($departments, $allPatients, &$usedSlots): void { // Pass $usedSlots by reference
                $user->assignRole('doctor');

                $doctor = Doctor::factory()
                    ->for($user)
                    ->create([
                        'department_id' => $departments->random(),
                    ]);

                $numAppointments = rand(5, 15);
                for ($i = 0; $i < $numAppointments; $i++) {
                    $patient = $allPatients->random();

                    $doctorId = $doctor->id;
                    $scheduledAt = null;
                    $slotKey = null;

                    // Generate unique slot for this doctor
                    do {
                        $date = fake()->dateTimeBetween('-1 month', '+3 months');
                        $scheduledAt = Carbon::parse($date)->setTime(
                            fake()->numberBetween(8, 17),
                            fake()->randomElement([0, 30])
                        );
                        $slotKey = $doctorId . '_' . $scheduledAt->format('Y-m-d H:i:s');
                    } while (isset($usedSlots[$slotKey]));

                    // Mark slot as used
                    $usedSlots[$slotKey] = true;

                    $status = fake()->randomElement(array_column(AppointmentStatus::cases(), 'value'));

                    $appointment = Appointment::factory()->create([
                        'doctor_id' => $doctorId,
                        'patient_id' => $patient->id,
                        'scheduled_at' => $scheduledAt,
                        'status' => $status,
                    ]);

                    if (in_array($status, [
                        AppointmentStatus::Arrived->value,
                        AppointmentStatus::InProgress->value,
                        AppointmentStatus::Completed->value,
                        AppointmentStatus::NoShow->value
                    ]) && rand(0, 1)) {
                        AppointmentSession::factory()->create([
                            'appointment_id' => $appointment->id,
                            'started_at' => $scheduledAt->copy()->addMinutes(rand(0, 15)),
                            'ended_at' => ($status === AppointmentStatus::Completed->value) ? $scheduledAt->copy()->addMinutes(rand(30, 90)) : null,
                            'status' => match ($status) {
                                AppointmentStatus::InProgress->value => 'in_progress',
                                AppointmentStatus::Completed->value => 'completed',
                                default => 'pending',
                            },
                        ]);
                    }
                }
            });
    }
}

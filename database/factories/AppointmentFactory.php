<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = array_column(AppointmentStatus::cases(), 'value');

        return [
            'doctor_id' => Doctor::factory(),
            'patient_id' => Patient::factory(),
            'scheduled_at' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'status' => fake()->randomElement($statuses),
            'notes' => fake()->optional(0.7)->text(100),
        ];
    }
}

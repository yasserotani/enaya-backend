<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AppointmentSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppointmentSession>
 */
class AppointmentSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'in_progress', 'completed', 'cancelled']);
        $startedAt = match ($status) {
            'pending' => null,
            'cancelled' => fake()->optional(0.5)->dateTimeBetween('-14 days', 'now'),
            default => fake()->dateTimeBetween('-14 days', 'now'),
        };

        $endedAt = null;
        if ($status === 'completed' && $startedAt) {
            $endedAt = (clone $startedAt)->modify('+'.fake()->numberBetween(15, 120).' minutes');
        }

        return [
            'appointment_id' => Appointment::factory(),
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'notes' => fake()->optional(0.7)->paragraph(),
            'patient_complaint' => fake()->optional(0.8)->sentence(),
            'diagnosis' => fake()->optional(0.7)->sentence(),
            'status' => $status,
        ];
    }
}

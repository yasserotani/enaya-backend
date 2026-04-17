<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Queue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Queue>
 */
class QueueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'patient_id' => Patient::factory(),
            'appointment_id' => null,
            'position' => fake()->numberBetween(1, 10),
            'status' => fake()->randomElement(['waiting', 'in_session', 'done']),
            'waiting_since' => now(),
            'notes' => fake()->optional(0.5)->text(50),
        ];
    }
}

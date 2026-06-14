<?php

namespace Database\Factories;

use App\Models\AppointmentSession;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prescription>
 */
class PrescriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'appointment_session_id' => AppointmentSession::factory(),
            'medication_name' => fake()->randomElement([
                'Amoxicillin',
                'Ibuprofen',
                'Metformin',
                'Lisinopril',
                'Omeprazole',
                'Aspirin',
                'Atorvastatin',
            ]),
            'dosage' => fake()->optional(0.7)->randomElement(['250mg', '500mg', '1000mg', '1 tablet', '2 tablets']),
            'frequency' => fake()->optional(0.7)->randomElement(['once daily', 'twice daily', 'three times daily', 'every 8 hours', 'as needed']),
            'duration_days' => fake()->optional(0.7)->numberBetween(3, 30),
            'instructions' => fake()->optional(0.7)->sentence(),
        ];
    }
}

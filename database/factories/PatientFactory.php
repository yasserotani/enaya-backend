<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = null;

        if (fake()->boolean(70)) {
            $user = User::factory()->create();
            $user->assignRole('patient');
        }
        return [
            'user_id' => $user?->id,
            'full_name' => fake()->unique()->name(),
            'phone' => fake()->unique()->numerify('+9639########'),
            'date_of_birth' => fake()->dateTimeBetween('-50 years', '-10 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['male', 'female']),
            'address' => fake()->address(),
            'job' => fake()->optional(0.6)->jobTitle(),
            'profile_completed' => $user !== null,
            'emergency_contact' => fake()->numerify('+9639########'),
        ];

    }
}

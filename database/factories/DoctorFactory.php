<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'department_id' => Department::factory(),
            'specialty' => fake()->randomElement([
                'General Practice',
                'Pediatrics',
                'Cardiology',
                'Dermatology',
            ]),
            'full_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'date_of_birth' => fake()->date(),
            'gender' => fake()->randomElement(['male', 'female']),
            'working_hours_start' => '08:00:00',
            'working_hours_end' => '16:00:00',
        ];
    }
}

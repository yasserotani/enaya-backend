<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()
            ->count(5)
            ->create()
            ->each(function (User $user): void {
                $user->assignRole('patient');

                Patient::create([
                    'user_id' => $user->id,
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'phone' => fake()->unique()->phoneNumber(),
                    'address' => fake()->address(),
                    'job' => fake()->optional(0.6)->jobTitle(),
                    'gender' => fake()->randomElement(['male', 'female']),
                    'age' => fake()->numberBetween(18, 80),
                ]);
            });
    }
}

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
                    'full_name' => $user->name,
                    'date_of_birth' => fake()->dateTimeBetween('-80 years', '-18 years'),
                    'gender' => fake()->randomElement(['male', 'female']),
                    'address' => fake()->address(),
                    'job' => fake()->optional(0.6)->jobTitle(),
                    'profile_completed' => fake()->boolean(),
                ]);
            });
    }
}

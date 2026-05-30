<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'user@enaya.com'],
            [
                'name' => 'Test User',
                'phone' => '1234567893',
                'password' => Hash::make('password'),
            ]
        );

        $user->assignRole('patient');

        Patient::firstOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $user->name,
                'date_of_birth' => fake()->dateTimeBetween('-80 years', '-18 years'),
                'gender' => fake()->randomElement(['male', 'female']),
                'address' => 'Test Address',
                'job' => 'Test Job',
                'profile_completed' => false,
            ]
        );
    }
}

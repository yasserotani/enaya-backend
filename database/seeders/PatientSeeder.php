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
                    'address' => fake()->address(),
                ]);
            });
    }
}

<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = Department::query()->pluck('id');

        User::factory()
            ->count(3)
            ->create()
            ->each(function (User $user) use ($departments): void {
                $user->assignRole('doctor');

                Doctor::create([
                    'user_id' => $user->id,
                    'department_id' => $departments->random(),
                    'specialty' => fake()->randomElement([
                        'General Practice',
                        'Pediatrics',
                        'Cardiology',
                        'Dermatology',
                    ]),
                ]);
            });
    }
}

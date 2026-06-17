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
            ->count(10)
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
                    'full_name' => $user->name,
                    'working_hours_start' => '08:00:00',
                    'working_hours_end' => '16:00:00',
                ]);
            });
    }
}

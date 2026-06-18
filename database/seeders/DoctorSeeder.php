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
        $departments = Department::query()->pluck('id');// get all departments ids

        User::factory()
            ->count(10)
            ->create()
            ->each(function (User $user) use ($departments): void {
                $user->assignRole('doctor');

                Doctor::factory()
                    ->for($user)
                    ->create([
                        'department_id' => $departments->random(),
                    ]);
            });
    }
}

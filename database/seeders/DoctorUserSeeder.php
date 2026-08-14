<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DoctorUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $department = Department::query()->first();

        if (! $department) {
            return;
        }

        $doctorUser = User::firstOrCreate(
            ['email' => 'doctor@enaya.com'],
            [
                'name' => 'Doctor',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $doctorUser->assignRole('doctor');

        Doctor::firstOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'department_id' => $department->id,
                'specialty' => 'General Practice',
                'full_name' => $doctorUser->name,
                'phone' => '1234567893',
                'date_of_birth' => fake()->dateTimeBetween('-65 years', '-20 years'),
                'gender' => 'male',
                'working_hours_start' => '08:00:00',
                'working_hours_end' => '16:00:00',
            ]
        );
    }
}

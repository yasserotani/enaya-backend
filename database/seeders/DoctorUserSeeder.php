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
                'phone' => '1234567892',
                'password' => Hash::make('password'),
            ]
        );

        $doctorUser->assignRole('doctor');

        Doctor::firstOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'department_id' => $department->id,
                'specialty' => 'General Practice',
            ]
        );
    }
}

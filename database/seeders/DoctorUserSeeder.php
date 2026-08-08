<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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

        $doctorUser = User::firstOrNew(['email' => 'doctor@enaya.com']);

        $doctorUser->name = 'Doctor';
        $doctorUser->password = Hash::make('password');
        $doctorUser->is_active = DB::raw('true');
        $doctorUser->save();

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

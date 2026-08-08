<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrNew(['email' => 'user@enaya.com']);

        $user->name = 'Test User';
        $user->password = Hash::make('password');
        $user->is_active = DB::raw('true');
        $user->save();

        $user->assignRole('patient');

        Patient::firstOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $user->name,
                'phone' => '1234567893',
                'date_of_birth' => fake()->dateTimeBetween('-80 years', '-18 years'),
                'gender' => fake()->randomElement(['male', 'female']),
                'address' => 'Test Address',
                'job' => 'Test Job',
                'profile_completed' => DB::raw('false'),
            ]
        );
    }
}

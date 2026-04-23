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
            ['address' => 'Test Address']
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ReceptionistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $receptionist = User::firstOrCreate(
            ['email' => 'receptionist@enaya.com'],
            [
                'name' => 'Receptionist',
                'phone' => '1234567891',
                'password' => Hash::make('password'),
            ]
        );

        $receptionist->assignRole('receptionist');
    }
}

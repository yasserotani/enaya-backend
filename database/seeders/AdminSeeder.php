<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@enaya.com'],
            [
                'name'     => 'Admin',
                'phone'    => '1234567890',
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole('admin');
    }
}

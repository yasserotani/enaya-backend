<?php

namespace Database\Seeders;

use App\Models\User;
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
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $admin->assignRole('admin');
    }
}

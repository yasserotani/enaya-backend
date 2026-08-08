<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrNew(['email' => 'admin@enaya.com']);

        $admin->name = 'Admin';
        $admin->password = Hash::make('password');
        $admin->is_active = DB::raw('true');
        $admin->save();

        $admin->assignRole('admin');
    }
}

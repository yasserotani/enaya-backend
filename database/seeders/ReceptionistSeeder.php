<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ReceptionistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $receptionist = User::firstOrNew(['email' => 'receptionist@enaya.com']);

        $receptionist->name = 'Receptionist';
        $receptionist->password = Hash::make('password');
        $receptionist->is_active = DB::raw('true');
        $receptionist->save();

        $receptionist->assignRole('receptionist');
    }
}

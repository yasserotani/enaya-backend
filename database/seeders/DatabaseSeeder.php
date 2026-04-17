<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AdminSeeder::class,
            DepartmentSeeder::class,
            DoctorSeeder::class,
            PatientSeeder::class,
            AppointmentSeeder::class,
            QueueSeeder::class

        ]);
        $receptionistUser = User::factory()->create([
            'name' => 'Receptionist User',
            'email' => 'receptionist@example.com',
        ]);
        $receptionistUser->assignRole('receptionist');


    }
}

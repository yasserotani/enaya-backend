<?php

namespace Database\Seeders;

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
//            UserSeeder::class,
            ReceptionistSeeder::class,
            DepartmentSeeder::class,
            DoctorUserSeeder::class,
            PatientSeeder::class,  // Run BEFORE DoctorSeeder so all patients exist
            DoctorSeeder::class,
            AppointmentSeeder::class,
            AppointmentSessionSeeder::class,
            PrescriptionSeeder::class,
        ]);

    }
}

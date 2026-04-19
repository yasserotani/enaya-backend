<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $receptionist = Role::firstOrCreate(['name' => 'receptionist']);
        $receptionist->givePermissionTo([
            'view-appointments',
            'create-appointment',
            'cancel-appointment',
            'manage-schedule',
            'view-queue',
            'manage-queue',
        ]);
        $admin->givePermissionTo(Permission::all());

        $doctor = Role::firstOrCreate(['name' => 'doctor']);
        $doctor->givePermissionTo([
            'view-appointments',
            'view-medical-records',
            'create-medical-records',
            'view-queue',
        ]);


        $patient = Role::firstOrCreate(['name' => 'patient']);
        $patient->givePermissionTo([
            'view-appointments',
            'create-appointment',
        ]);
    }
}

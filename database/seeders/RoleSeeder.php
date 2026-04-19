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
        $receptionist = Role::firstOrCreate(['name' => 'receptionist', 'guard_name' => 'web']);
        $receptionist->givePermissionTo([
            'view-appointments',
            'create-appointment',
            'cancel-appointment',
            'manage-schedule',
            'view-queue',
            'manage-queue',
        ]);

        $doctor = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);
        $doctor->givePermissionTo([
            'view-appointments',
            'view-medical-records',
            'create-medical-records',
            'view-queue',
        ]);


        $patient = Role::firstOrCreate(['name' => 'patient', 'guard_name' => 'web']);
        $patient->givePermissionTo([
            'view-appointments',
            'create-appointment',
        ]);

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo(Permission::all());
    }
}

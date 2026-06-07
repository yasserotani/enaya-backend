<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'manage-users',
            'manage-doctors',
            'manage-schedule',
            'manage-receptionists',
            'manage-departments',
            'create-appointment',
            'view-appointments',
            'cancel-appointment',
            'write-prescription',
            'view-prescription',
            'create-medical-records',
            'view-medical-records',
            'view-queue',
            'manage-queue',
            'view-reports',

            // patient management
            'view-patients',
            'create-patient',
            'edit-patient',
            'delete-patient',
            'edit-app-patients',    // edit patients who have a user account
            'delete-app-patients',  // delete patients who have a user account

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}

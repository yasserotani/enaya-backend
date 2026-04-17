<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'view-appointments',
            'create-appointment',
            'cancel-appointment',
            'view-medical-records',
            'create-medical-records',
            'manage-schedule',
            'manage-doctors',
            'manage-receptionists',
            'manage-departments',
            'view-queue',
            'manage-queue',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}

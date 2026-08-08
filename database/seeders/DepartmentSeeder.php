<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Cardiology',
            'Neurology',
            'Pediatrics',
            'Orthopedics',
            'Radiology',
            'Dermatology',
            'Ophthalmology',
            'Gynecology',
            'Oncology',
            'Urology',
            'General Surgery',
        ];

        foreach ($departments as $name) {
            Department::create(['name' => $name]);
        }
    }
}

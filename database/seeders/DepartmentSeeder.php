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
            'General Medicine',
            'Pediatrics',
            'Cardiology',
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(['name' => $department]);
        }
    }
}

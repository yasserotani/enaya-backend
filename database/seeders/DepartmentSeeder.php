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

        $now = now();
        $rows = array_map(function ($name) use ($now) {
            return ['name' => $name, 'created_at' => $now, 'updated_at' => $now];
        }, $departments);

        foreach (array_chunk($rows, 200) as $chunk) {
            \Illuminate\Support\Facades\DB::table((new Department)->getTable())->insert($chunk);
        }
    }
}

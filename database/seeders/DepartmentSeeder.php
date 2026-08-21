<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        ];

        $now = now();
        $rows = array_map(function ($name) use ($now) {
            return ['name' => $name, 'created_at' => $now, 'updated_at' => $now];
        }, $departments);

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table((new Department)->getTable())->insert($chunk);
        }
    }
}

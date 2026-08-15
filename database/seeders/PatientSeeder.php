<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = Patient::factory()->count(150)->make()->toArray();
        $now = now();
        $rows = array_map(function ($r) use ($now) {
            $r['created_at'] = $now;
            $r['updated_at'] = $now;
            return $r;
        }, $rows);

        foreach (array_chunk($rows, 200) as $chunk) {
            \Illuminate\Support\Facades\DB::table((new Patient)->getTable())->insert($chunk);
        }

    }
}

<?php

namespace Database\Seeders;

use App\Models\Patient;
use Carbon\Carbon;
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
            // Normalize date_of_birth to SQL DATE format (Y-m-d) because factories may produce ISO-8601 strings
            if (isset($r['date_of_birth']) && $r['date_of_birth'] !== null) {
                try {
                    $r['date_of_birth'] = Carbon::parse($r['date_of_birth'])->format('Y-m-d');
                } catch (\Throwable $e) {
                    $r['date_of_birth'] = null;
                }
            }

            $r['created_at'] = $now;
            $r['updated_at'] = $now;
            return $r;
        }, $rows);

        foreach (array_chunk($rows, 200) as $chunk) {
            \Illuminate\Support\Facades\DB::table((new Patient)->getTable())->insert($chunk);
        }

    }
}

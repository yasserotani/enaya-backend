<?php

namespace App\Database;

use Illuminate\Database\PostgresConnection;

class CustomPostgresConnection extends PostgresConnection
{
    public function prepareBindings(array $bindings): array
    {
        \Log::info('prepareBindings called - before', ['bindings' => $bindings]);

        // Cast PHP booleans to Postgres boolean literals BEFORE parent
        // has a chance to coerce them to int under emulated prepares.
        $bindings = array_map(function ($value) {
            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }
            return $value;
        }, $bindings);

        $bindings = parent::prepareBindings($bindings);

        \Log::info('prepareBindings called - after', ['bindings' => $bindings]);

        return $bindings;
    }
}

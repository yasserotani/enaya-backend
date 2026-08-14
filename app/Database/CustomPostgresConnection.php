<?php

namespace App\Database;

use Illuminate\Database\PostgresConnection;

class CustomPostgresConnection extends PostgresConnection
{
    /**
     * Convert PHP booleans to native Postgres booleans before binding.
     *
     * PDO's pgsql driver incorrectly casts PHP bool to integer (1/0) when
     * ATTR_EMULATE_PREPARES is true (required by Supabase's PgBouncer).
     * Postgres then rejects the integer for a boolean column.
     */
    public function prepareBindings(array $bindings): array
    {
        $bindings = parent::prepareBindings($bindings);

        logger('prepareBindings called', $bindings);

        return array_map(function ($value) {
            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            return $value;
        }, $bindings);
    }
}

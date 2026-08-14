<?php

namespace App\Providers;

use App\Database\CustomPostgresConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Fix PDO pgsql driver casting PHP booleans to integers (1/0) when
        // ATTR_EMULATE_PREPARES is true (required by Supabase's PgBouncer).
        // This ensures native Postgres true/false is sent for boolean columns.
        DB::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            return new CustomPostgresConnection($connection, $database, $prefix, $config);
        });
    }
}

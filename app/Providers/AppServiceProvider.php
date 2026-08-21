<?php

namespace App\Providers;

use App\Database\CustomPostgresConnection;
use App\Notifications\UserLoggedInNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Event;
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

        // Listen for successful logins and notify the user (database + FCM)
        Event::listen(Login::class, function (Login $event) {
            try {
                $user = $event->user;
                if ($user) {
                    $ip = request()->ip();
                    $agent = request()->userAgent();
                    $user->notify(new UserLoggedInNotification($ip, $agent));
                }
            } catch (\Throwable $e) {
                report($e);
            }
        });

        // Fix PDO pgsql driver casting PHP booleans to integers (1/0) when
        // ATTR_EMULATE_PREPARES is true (required by Supabase's PgBouncer).
        // This ensures native Postgres true/false is sent for boolean columns.
        Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            return new CustomPostgresConnection($connection, $database, $prefix, $config);
        });
    }
}

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// deployment
Route::any('/deploy/migrate', function (Request $request) {
    if ($request->query('secret') !== env('DEPLOY_SECRET')
        && $request->header('X-Deploy-Secret') !== env('DEPLOY_SECRET')) {
        abort(403);
    }
    try {
        Artisan::call('migrate', ['--force' => true]);

        return response("SUCCESS:\n" . Artisan::output(), 200)
            ->header('Content-Type', 'text/plain');
    } catch (Throwable $e) {
        return response("ERROR:\n" . $e->getMessage(), 500)
            ->header('Content-Type', 'text/plain');
    }
});

Route::any('/deploy/seed', function (Request $request) {
    if ($request->query('secret') !== env('DEPLOY_SECRET')
        && $request->header('X-Deploy-Secret') !== env('DEPLOY_SECRET')) {
        abort(403);
    }
    try {
        $seeder = $request->query('seeder');
        $params = ['--force' => true];

        if ($seeder) {
            $class = $seeder;
            // Accept short class names like "DoctorSeeder" and expand to Database\Seeders\DoctorSeeder
            if (! str_contains($class, '\\')) {
                $class = 'Database\\Seeders\\' . $class;
            }

            if (! class_exists($class)) {
                return response("ERROR:\n Seeder class {$class} not found", 400)
                    ->header('Content-Type', 'text/plain');
            }

            $params['--class'] = $class;
        }

        Artisan::call('db:seed', $params);

        return response("SUCCESS:\n" . Artisan::output(), 200)
            ->header('Content-Type', 'text/plain');
    } catch (Throwable $e) {
        return response("ERROR:\n" . $e->getMessage(), 500)
            ->header('Content-Type', 'text/plain');
    }
});

Route::any('/deploy/fresh', function (Request $request) {
    if ($request->query('secret') !== env('DEPLOY_SECRET')
        && $request->header('X-Deploy-Secret') !== env('DEPLOY_SECRET')) {
        abort(403);
    }
    try {
        Artisan::call('migrate:fresh', ['--force' => true]);

        return response("SUCCESS:\n" . Artisan::output(), 200)
            ->header('Content-Type', 'text/plain');
    } catch (Throwable $e) {
        return response("ERROR:\n" . $e->getMessage(), 500)
            ->header('Content-Type', 'text/plain');
    }
});
Route::any('/deploy/clear-route', function (Request $request) {
    if ($request->query('secret') !== env('DEPLOY_SECRET')
        && $request->header('X-Deploy-Secret') !== env('DEPLOY_SECRET')) {
        abort(403);
    }
    try {
        Artisan::call('route:clear');

        return response("SUCCESS: Route cache cleared!\n" . Artisan::output(), 200)
            ->header('Content-Type', 'text/plain');
    } catch (Throwable $e) {
        return response("ERROR:\n" . $e->getMessage(), 500)
            ->header('Content-Type', 'text/plain');
    }
});
Route::any('/deploy/routes', function (Request $request) {
    if ($request->query('secret') !== env('DEPLOY_SECRET')
        && $request->header('X-Deploy-Secret') !== env('DEPLOY_SECRET')) {
        abort(403);
    }

    // Get all registered routes in the system
    $routes = collect(Route::getRoutes())->map(function ($route) {
        return [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
        ];
    });

    return response()->json($routes, 200, [], JSON_PRETTY_PRINT);
});
Route::any('/deploy/clear-all', function (Request $request) {
    if ($request->query('secret') !== env('DEPLOY_SECRET')
        && $request->header('X-Deploy-Secret') !== env('DEPLOY_SECRET')) {
        abort(403);
    }
    try {
        $output = '';

        Artisan::call('config:clear');
        $output .= "config:clear\n" . Artisan::output() . "\n";

        Artisan::call('cache:clear');
        $output .= "cache:clear\n" . Artisan::output() . "\n";

        Artisan::call('route:clear');
        $output .= "route:clear\n" . Artisan::output() . "\n";

        Artisan::call('view:clear');
        $output .= "view:clear\n" . Artisan::output() . "\n";

        return response("SUCCESS:\n" . $output, 200)
            ->header('Content-Type', 'text/plain');
    } catch (Throwable $e) {
        return response("ERROR:\n" . $e->getMessage(), 500)
            ->header('Content-Type', 'text/plain');
    }
});
// https://enaya-backend.vercel.app/deploy/migrate?secret=enayasecret
// https://enaya-backend.vercel.app/deploy/seed?secret=enayasecret
// https://enaya-backend.vercel.app/deploy/fresh?secret=enayasecret
// https://enaya-backend.vercel.app/deploy/clear-route?secret=enayasecret
// https://enaya-backend.vercel.app/deploy/routes?secret=enayasecret
// https://enaya-backend.vercel.app/deploy/clear-all?secret=enayasecret

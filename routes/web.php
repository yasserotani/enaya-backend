<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

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
    } catch (\Throwable $e) {
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
        Artisan::call('db:seed', ['--force' => true]);
        return response("SUCCESS:\n" . Artisan::output(), 200)
            ->header('Content-Type', 'text/plain');
    } catch (\Throwable $e) {
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
        Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
        return response("SUCCESS:\n" . Artisan::output(), 200)
            ->header('Content-Type', 'text/plain');
    } catch (\Throwable $e) {
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
    } catch (\Throwable $e) {
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

//https://enaya-backend.vercel.app/deploy/migrate?secret=enayasecret
//https://enaya-backend.vercel.app/deploy/seed?secret=enayasecret
//https://enaya-backend.vercel.app/deploy/fresh?secret=enayasecret
//https://enaya-backend.vercel.app/deploy/clear-route?secret=enayasecret
//https://enaya-backend.vercel.app/deploy/routes?secret=enayasecret

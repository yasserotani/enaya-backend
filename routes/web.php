<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//deployment

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

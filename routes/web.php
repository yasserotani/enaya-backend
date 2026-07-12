<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::any('/deploy/migrate', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return response("SUCCESS:\n" . Artisan::output(), 200)
            ->header('Content-Type', 'text/plain');
    } catch (\Throwable $e) {
        // This catches the error before Laravel can build the HTML page
        return response(
            "THE REAL ERROR IS:\n" . $e->getMessage(),
            500
        )->header('Content-Type', 'text/plain');
    }
});

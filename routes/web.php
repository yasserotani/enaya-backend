<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/deploy/migrate', function (Illuminate\Http\Request $request) {
    if ($request->header('X-Deploy-Secret') !== env('DEPLOY_SECRET')) {
        abort(403);
    }

    Artisan::call('migrate', ['--force' => true]);

    return response()->json(['output' => Artisan::output()]);
});

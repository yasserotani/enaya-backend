<?php

use App\Http\Controllers\Doctor\DoctorController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('doctors', DoctorController::class);
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;
Route::get('/user', function (Request $request) {
    return "hello world";
})->middleware('auth:sanctum');
require __DIR__ . '/api/auth.php';

//patient routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/patient_profile', [PatientController::class, 'show']);
    Route::post('/complete_patient_profile', [PatientController::class, 'store']);
    Route::put('/patient_update', [PatientController::class, 'update']);
});
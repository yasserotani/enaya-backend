<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\DoctorResource;

class DoctorController extends Controller
{
    /**
     * Display a listing of the doctors.
     */
    public function index(): JsonResponse
    {
        $doctors = Doctor::all();

        return response()->json([
            'success' => true,
            'data' => DoctorResource::collection($doctors),
            'message' => 'Doctors retrieved successfully.',
        ]);
    }

    /**
     * Display the specified doctor.
     */
    public function show(Doctor $doctor): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new DoctorResource($doctor),
            'message' => 'Doctor retrieved successfully.',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\DoctorResource;
use App\Models\Department;
// Import the Department model
use App\Models\Doctor;
use App\Services\DoctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Assuming a DepartmentResource exists or will be created

class DoctorController extends Controller
{
    protected $doctorService;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService;
    }

    /**
     * Display a listing of the doctors for receptionists.
     */
    public function index(Request $request): JsonResponse
    {
        $doctors = $this->doctorService->indexDoctors($request);

        return response()->json([
            'success' => true,
            'data' => $doctors,
            'error' => null,
        ]);
    }

    /**
     * Display the specified doctor for receptionists.
     */
    public function show(Doctor $doctor): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new DoctorResource($doctor),
            'message' => 'Doctor retrieved successfully.',
        ]);
    }

    /**
     * Display a listing of all departments.
     */
    public function getDepartments(): JsonResponse
    {
        $departments = Department::all();

        return response()->json([
            'success' => true,
            'data' => DepartmentResource::collection($departments),
            'message' => 'Departments retrieved successfully.',
        ]);
    }
}

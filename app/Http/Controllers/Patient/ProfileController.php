<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\CompletePatientProfileRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\PatientResource;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $patient = $request->user()->patient;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new PatientResource($patient->load('user')),
        ]);
    }

    public function complete(CompletePatientProfileRequest $request)
    {
        $validated = $request->validated();
        $patient = $request->user()->patient;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient record not found',
            ], 404);
        }

        if ($patient->profile_completed) {
            return response()->json([
                'success' => false,
                'message' => 'Profile already completed',
            ], 403);
        }

        $patient->update([
            'full_name' => $validated['full_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'phone' => $validated['phone'],
            'gender' => $validated['gender'],
            'address' => $validated['address'] ?? null,
            'job' => $validated['job'] ?? null,
            'profile_completed' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile completed successfully',
            'data' => new PatientResource($patient->fresh()->load('user')),
        ]);
    }

    public function update(UpdatePatientRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = $request->user();
            $patient = $user->patient;

            $user->update($request->only(['name', 'email']));
            $patient->update($request->only(['phone', 'date_of_birth', 'gender', 'address', 'job']));


            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => new PatientResource($patient->fresh()->load('user')),
            ]);
        });
    }

//    public function getDepartmentDoctors(Request $request)
//    {
//        $request->validate([
//            'department' => 'required',
//        ]);
//
//        $departmentInput = $request->department;
//
//        $doctors = is_numeric($departmentInput)
//            ? Doctor::where('department_id', $departmentInput)->with('department')->get()
//            : Doctor::whereHas('department', fn($q) => $q->where('name', $departmentInput))
//                ->with('department')
//                ->get();
//
//        return response()->json([
//            'success' => true,  // was missing
//            'message' => 'Doctors retrieved successfully',
//            'data' => DoctorResource::collection($doctors),
//        ]);
//    }
}

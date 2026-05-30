<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $patient = $request->user()->patient;

        if (! $patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $patient,
        ]);
    }

    public function store(StorePatientRequest $request)
    {
        $patient = $request->user()->patient;

        if (! $patient) {
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
            ...$request->validated(),
            'profile_completed' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Patient profile completed successfully',
            'data' => $patient->fresh(),
        ]);
    }

    public function update(UpdatePatientRequest $request)
    {
        $patient = $request->user()->patient;

        if (! $patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient record not found',
            ], 404);
        }

        $patient->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $patient->fresh(),
        ]);
    }
}

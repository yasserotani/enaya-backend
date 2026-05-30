<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StoreReceptionPatientRequest;
use App\Models\Patient;

class ReceptionPatientController extends Controller
{
    public function index()
    {
        $patients = Patient::whereNull('user_id')->get();

        return response()->json([
            'success' => true,
            'data' => $patients,

        ]);
    }

    public function store(StoreReceptionPatientRequest $request)
    {
        $patient = Patient::create([
            ...$request->validated(),
            'user_id' => null,
            'profile_completed' => true,

        ]);

        return response()->json([
            'success' => true,
            'message' => 'Patient registered successfully',
            'data' => $patient,
        ], 201);
    }

    public function update(StoreReceptionPatientRequest $request, Patient $patient)
    {
        if ($patient->user_id !== null) {
            return response()->json([
                'success' => false,
                'message' => 'This patient has an account and cannot be edited from reception',
            ], 403);
        }

        $patient->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Patient updated successfully',
            'data' => $patient->fresh(),
        ]);
    }

    public function destroy(Patient $patient)
    {
        if ($patient->user_id !== null) {
            return response()->json([
                'success' => false,
                'message' => 'This patient has an account and cannot be deleted from reception',
            ], 403);
        }

        $patient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Patient deleted successfully',
        ]);
    }
}

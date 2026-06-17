<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StoreReceptionPatientRequest;
use App\Http\Requests\Patient\UpdateReceptionPatientRequest;
use App\Models\Patient;

class ReceptionPatientController extends Controller
{
    public function index()
    {
        $patients = Patient::applyFilters(request()->only([
            'search',
            'gender',
            'has_account',
        ]))
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $patients,
        ]);
    }

    public function show(Patient $patient)
    {
        return response()->json([
            'success' => true,
            'data' => $patient,
        ]);
    }

    public function store(StoreReceptionPatientRequest $request)
    {
        // check if the patient with this phone number already exist

        $existingPatient = Patient::query()
            ->where('phone', '=', $request->validated('phone'))
            ->first();
        if ($existingPatient) {
            return response()->json([
                'success' => false,
                'message' => 'A patient with this phone number already exists',
                'data' => $existingPatient,
            ], 409);
        }

        // create the patient
        $patient = Patient::create([
            ...$request->validated(),
            'user_id' => null,
            'profile_completed' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Patient created successfully',
            'data' => $patient,
        ], 201);
    }

    public function update(UpdateReceptionPatientRequest $request, Patient $patient)
    {
        // if the patient has an account, prevent updating from reception
        if ($patient->user_id !== null && !$request->user()->can('edit-app-patients')) {
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

        if ($patient->user_id !== null && !request()->user()->can('delete-app-patients')) {
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

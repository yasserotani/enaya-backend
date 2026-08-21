<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reception\StorePatientRequest;
use App\Http\Requests\Reception\UpdatePatientRequest;
use App\Models\Patient;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::applyFilters(request()->only([
            'search',
            'gender',
            'has_account',
            'profile_completed',
            'created_from',
            'created_to',
            'birth_from',
            'birth_to',
        ]))
            ->when(request()->boolean('with_trashed'), fn ($query) => $query->withTrashed())
            ->latest()
            ->paginate(20);

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

    public function store(StorePatientRequest $request)
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
            'profile_completed' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Patient created successfully',
            'data' => $patient,
        ], 201);
    }

    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        // if the patient has an account, prevent updating from reception
        if ($patient->user_id !== null && ! $request->user()->can('edit-app-patients')) {
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

        if ($patient->user_id !== null && ! request()->user()->can('delete-app-patients')) {
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

    public function restore(Patient $patient)
    {
        $patient->restore();

        return response()->json([
            'success' => true,
            'message' => 'Patient restored successfully.',
            'data' => $patient->fresh(),
        ]);
    }

    public function forceDelete(Patient $patient)
    {
        $patient->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Patient permanently deleted successfully.',
        ]);
    }
}

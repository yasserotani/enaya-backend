<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StoreReceptionPatientRequest;
use App\Http\Requests\Patient\UpdateReceptionPatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'gender',
            'has_account',
            'profile_completed',
            'created_from',
            'created_to',
            'birth_from',
            'birth_to',
        ]);

        $patients = Patient::with('user')
            ->applyFilters($filters)
            ->latest()
            ->paginate(20);
        return response()->json([
            'success' => true,
//            'data' => PatientResource::collection($patients),
            'data' => $patients,
        ]);
    }

    public function show(Patient $patient)
    {
        return response()->json([
            'success' => true,
            'data' => new PatientResource($patient->load('user')),
        ]);
    }

    public function store(StoreReceptionPatientRequest $request)
    {
        $existingPatient = Patient::where('phone', $request->validated('phone'))->first();

        if ($existingPatient) {
            return response()->json([
                'success' => false,
                'message' => 'A patient with this phone number already exists.',
                'data' => $existingPatient,
            ], 409);
        }

        $patient = Patient::create([
            ...$request->validated(),
            'user_id' => null,
            'profile_completed' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Patient created successfully.',
            'data' => new PatientResource($patient->load('user')),
        ], 201);
    }

    public function update(UpdateReceptionPatientRequest $request, Patient $patient)
    {
        // admin can edit any patient, no restrictions unlike reception
        $patient->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Patient updated successfully.',
            'data' => new PatientResource($patient->fresh()),
        ]);
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Patient deleted successfully.',
        ]);
    }
}

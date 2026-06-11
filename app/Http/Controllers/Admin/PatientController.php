<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StoreReceptionPatientRequest;
use App\Http\Requests\Patient\UpdateReceptionPatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    // GET /api/admin/patients
    public function index(Request $request)
    {
        $patients = Patient::with('user')->applyFilters($request->only([
            'search',
            'gender',
            'has_account',
        ]))
            ->latest()
            ->paginate(20); // admin gets pagination unlike reception's ->get()

        return response()->json([
            'success' => true,
            'data' => $patients,
        ]);
    }

    // GET /api/admin/patients/{patient}
    public function show(Patient $patient)
    {
        return response()->json([
            'success' => true,
            'data' => new PatientResource($patient->load('user')),
        ]);
    }

    // POST /api/admin/patients
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
            'data' => $patient,
        ], 201);
    }

    // PUT /api/admin/patients/{patient}
    public function update(UpdateReceptionPatientRequest $request, Patient $patient)
    {
        // admin can edit any patient, no restrictions unlike reception
        $patient->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Patient updated successfully.',
            'data' => $patient->fresh(),
        ]);
    }

    // DELETE /api/admin/patients/{patient}
    public function destroy(Patient $patient)
    {
        $patient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Patient deleted successfully.',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatientResource;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request, ?Doctor $doctor = null)
    {
        // Keep the doctor route available for compatibility, but the list is no longer restricted
        // to patients assigned to a specific doctor.
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

        $patients = Patient::withCount('appointments')
            ->applyFilters($filters)
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Patients fetched successfully',
            'data' => PatientResource::collection($patients->items()),
            'meta' => [
                'current_page' => $patients->currentPage(),
                'last_page' => $patients->lastPage(),
                'per_page' => $patients->perPage(),
                'total' => $patients->total(),
            ],
        ]);
    }

    public function show(Patient $patient)
    {
        $authDoctor = auth()->user()->doctor;

        $patient->loadMissing([
            'appointments' => fn($q) => $q
                ->where('doctor_id', $authDoctor->id)
                ->with('appointmentSession.prescriptions')
                ->latest('scheduled_at'),
        ]);

        return response()->json([
            'success' => true,
            'data' => new PatientResource($patient),
        ]);
    }
}

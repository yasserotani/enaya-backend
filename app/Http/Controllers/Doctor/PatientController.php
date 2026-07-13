<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatientResource;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $authDoctor = auth()->user()->doctor;

        // apply the same patient-level filters used by reception
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

        // get all patients that have appointments with this doctor, then apply patient filters

        $patients = Patient::withCount('appointments')
            ->whereHas('appointments', function ($q) use ($authDoctor) {
                $q->where('doctor_id', $authDoctor->id);
            })
            ->applyFilters($filters)
            ->latest()
            ->paginate(15);


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

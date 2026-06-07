<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatientResource;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Doctor $doctor, Request $request)
    {
        // apply the same patient-level filters used by reception
        $filters = $request->only([
            'search',
            'gender',
            'has_account',
            'profile_completed',
            'created_from',
            'created_to',
            'dob_from',
            'dob_to',
        ]);

        // get all patients that have appointments with this doctor, then apply patient filters
        $patients = Patient::applyFilters($filters)
            ->whereHas('appointments', function ($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id);
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => PatientResource::collection($patients),
        ]);
    }
}

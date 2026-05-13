<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequest;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\ReceptionPatientRequest;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PatientRequest $request)
    {
        $user = $request->user();

        $patient = $user->patient;


        if ($patient->profile_completed) {
            return response()->json([
                'success' => false,
                'message' => 'Profile already completed'
            ], 403);
        } else {
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
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $patient = $request->user()->patient;

        if (!$patient) {
            return response()->json([
                'message' => 'Patient profile not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $patient,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function store_reception_patient(ReceptionPatientRequest $request)
    {
        $patient = Patient::create([
            ...$request->validated(),
            'user_id' => null,
            'profile_completed' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Patient registered from reception',
            'data' => $patient,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PatientRequest $request)
    {
        $patient = Patient::where('user_id', $request->user()->id)->firstOrFail();

        $patient->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $patient->fresh(),
        ]);
    }
    public function update_reception_patient(ReceptionPatientRequest $request, $id)
    {
        $patient = Patient::where('id', $id)
            ->whereNull('user_id') 
            ->firstOrFail();

        $patient->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Reception patient updated successfully',
            'data' => $patient->fresh(),
        ]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patients)
    {
        //
    }
}

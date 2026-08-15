<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Resources\PrescriptionResource;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $patientId = $request->user()->patient->id;

        $prescriptions = Prescription::whereHas('appointmentSession.appointment', function ($query) use ($patientId) {
            $query->where('patient_id', $patientId);
        })
            ->with(['appointmentSession.appointment.doctor'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => PrescriptionResource::collection($prescriptions->items()),
            'meta' => [
                'current_page' => $prescriptions->currentPage(),
                'last_page' => $prescriptions->lastPage(),
                'per_page' => $prescriptions->perPage(),
                'total' => $prescriptions->total(),
            ],
        ]);
    }

    public function show(Request $request, Prescription $prescription)
    {
        $ownerId = $prescription->appointmentSession->appointment->patient_id;

        // check if the user is the owner if this prescription
        if ($ownerId !== $request->user()->patient->id) {
            return response()->json([
                'success' => false,
                'error' => 'You are not authorized to view this prescription.',
                'errorCode' => '403',
            ], 403);
        }

        $prescription->load(['appointmentSession.appointment.doctor']);

        return response()->json([
            'success' => true,
            'data' => new PrescriptionResource($prescription),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentSessionResource;
use App\Models\AppointmentSession;
use Illuminate\Http\Request;

class AppointmentSessionController extends Controller
{
    public function index(Request $request)
    {
        $patientId = $request->user()->patient->id;

        $sessions = AppointmentSession::whereHas('appointment', function ($query) use ($patientId) {
            $query->where('patient_id', $patientId);
        })
            ->with(['appointment.doctor', 'prescriptions'])
            ->orderBy('started_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => AppointmentSessionResource::collection($sessions),
        ]);
    }

    public function show(Request $request, AppointmentSession $session)
    {
        // check if the session belong to the pateint
        if ($session->appointment->patient_id !== $request->user()->patient->id) {
            return response()->json([
                'success' => false,
                'error' => 'You are not authorized to view this medical record.',
                'errorCode' => '403',
            ], 403);
        }

        $session->load(['appointment.doctor', 'prescriptions']);

        return response()->json([
            'success' => true,
            'data' => new AppointmentSessionResource($session),
        ]);
    }
}

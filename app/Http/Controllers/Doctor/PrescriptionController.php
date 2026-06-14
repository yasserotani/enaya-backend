<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\AppointmentSession;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function store(Request $request, AppointmentSession $session)
    {
        $request->validate([
            'medication_name' => ['required', 'string', 'max:255'],
            'dosage' => ['required', 'string', 'max:255'],
            'frequency' => ['required', 'string', 'max:255'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'instructions' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->checkSessionOwnership($session, $request->user()->doctor);
        $this->checkSessionActive($session);

        $prescription = $session->prescriptions()->create($request->only([
            'medication_name',
            'dosage',
            'frequency',
            'duration_days',
            'instructions',
        ]));

        return response()->json([
            'success' => true,
            'data' => ['prescription' => $prescription],
            'error' => null,
            'errorCode' => null,
        ], 201);
    }

    private function checkSessionOwnership(AppointmentSession $session, $doctor): void
    {
        // check if the session is owned to the doctor
        if ($session->appointment->doctor_id !== $doctor->id) {
            abort(response()->json([
                'success' => false,
                'data' => null,
                'error' => 'This session does not belong to you.',
                'errorCode' => '403',
            ], 403));
        }
    }

    private function checkSessionActive(AppointmentSession $session): void
    {
        // check if the session is active
        if ($session->status !== 'active') {
            abort(response()->json([
                'success' => false,
                'data' => null,
                'error' => 'Cannot modify prescriptions on a closed session.',
                'errorCode' => 'SESSION_NOT_ACTIVE',
            ], 422));
        }
    }

    public function destroy(Request $request, AppointmentSession $session, Prescription $prescription)
    {
        $this->checkSessionOwnership($session, $request->user()->doctor);
        $this->checkSessionActive($session);

        // Make sure the prescription actually belongs to this session
        if ($prescription->appointment_session_id !== $session->id) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => 'Prescription does not belong to this session.',
                'errorCode' => '404',
            ], 404);
        }

        $prescription->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'error' => null,
            'errorCode' => null,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StorePrescriptionRequest;
use App\Http\Requests\Doctor\UpdatePrescriptionRequest;
use App\Models\AppointmentSession;
use App\Models\Prescription;
use App\Notifications\NewPrescriptionNotification;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function store(StorePrescriptionRequest $request, AppointmentSession $session)
    {
        $this->checkSessionOwnership($session, $request->user()->doctor);
        $this->checkSessionActive($session);

        $prescription = $session->prescriptions()->create($request->validated());

        if ($session->appointment?->patient?->user) {
            $session->appointment->patient->user->notify(
                new NewPrescriptionNotification($prescription)
            );
        }

        return response()->json([
            'success' => true,
            'data' => ['prescription' => $prescription],
            'error' => null,
            'errorCode' => null,
        ], 201);
    }

    public function update(UpdatePrescriptionRequest $request, AppointmentSession $session, Prescription $prescription)
    {
        $this->checkSessionOwnership($session, $request->user()->doctor);
        $this->checkSessionActive($session);

        if ($prescription->appointment_session_id !== $session->id) {
            return response()->json([
                'success' => false,
                'error' => 'Prescription does not belong to this session',
            ], 404);
        }

        $prescription->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => ['prescription' => $prescription->fresh()],
        ]);
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

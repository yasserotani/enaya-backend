<?php

namespace App\Http\Controllers\Doctor;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\EndSessionRequest;
use App\Http\Requests\Doctor\StartSessionRequest;
use App\Http\Requests\Doctor\UpdateSessionRequest;
use App\Models\Appointment;
use App\Models\AppointmentSession;
use App\Notifications\SessionStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentSessionController extends Controller
{
    public function index(Request $request, Appointment $appointment)
    {
        $this->checkOwnership($appointment, $request->user()->doctor);

        $sessions = $appointment->sessions()->with('prescriptions')->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => ['sessions' => $sessions->items()],
            'meta' => [
                'current_page' => $sessions->currentPage(),
                'last_page' => $sessions->lastPage(),
                'per_page' => $sessions->perPage(),
                'total' => $sessions->total(),
            ],
            'error' => null,
            'errorCode' => null,
        ]);
    }

    private function checkOwnership(Appointment $appointment, $doctor)
    {
        // function to check if the doctor own this appointment
        if ($appointment->doctor_id !== $doctor->id) {
            abort(response()->json([
                'success' => false,
                'data' => null,
                'error' => 'This appointment does not belong to you.',
                'errorCode' => '403',
            ], 403));
        }
    }

    public function start(StartSessionRequest $request, Appointment $appointment)
    {
        // check if the appointment is for this doctor
        $this->checkOwnership($appointment, $request->user()->doctor);

        // check if the appointment is in a valid status to start a session
        if (! in_array($appointment->status, [
            AppointmentStatus::Arrived,
            AppointmentStatus::Confirmed,
            AppointmentStatus::Scheduled,
        ], true)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => "Cannot start a session for a '{$appointment->status->value}' appointment.",
                'errorCode' => '422',
            ], 422);
        }

        // check if already an active session for this appointment
        if ($appointment->sessions()->where('status', 'active')->exists()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => 'An active session already exists for this appointment.',
                'errorCode' => '422',
            ], 422);
        }

        $session = DB::transaction(function () use ($request, $appointment) {
            $session = $appointment->sessions()->create([
                'started_at' => now(),
                'status' => 'in_progress',
                'patient_complaint' => $request->patient_complaint ?? null,
                'notes' => $request->notes,
            ]);

            $appointment->update(['status' => AppointmentStatus::InProgress]);

            return $session;
        });

        if ($appointment->patient?->user) {
            $appointment->patient->user->notify(new SessionStatusNotification($session));
        }

        return response()->json([
            'success' => true,
            'data' => ['session' => $session],
            'error' => null,
            'errorCode' => null,
        ], 201);
    }

    public function update(UpdateSessionRequest $request, Appointment $appointment, AppointmentSession $session)
    {
        $this->checkOwnership($appointment, $request->user()->doctor);

        // can't edit a finished session
        if ($session->status === 'completed') {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => 'Cannot modify a completed session.',
                'errorCode' => '422',
            ], 422);
        }

        DB::transaction(function () use ($request, $appointment, $session) {
            $isClosing = in_array($request->status, ['completed', 'cancelled'], true);

            $session->update([
                ...$request->only(['diagnosis', 'patient_complaint', 'notes', 'status']),
                'ended_at' => $isClosing ? now() : $session->ended_at,
            ]);

            if ($request->status === 'completed') {
                $appointment->update(['status' => AppointmentStatus::Completed]);
            } elseif ($request->status === 'cancelled') {
                $appointment->update(['status' => AppointmentStatus::Canceled]);
            }
        });

        return response()->json([
            'success' => true,
            'data' => ['session' => $session->fresh('prescriptions')],
            'error' => null,
            'errorCode' => null,
        ]);
    }

    public function end(EndSessionRequest $request, Appointment $appointment, AppointmentSession $session)
    {
        $this->checkOwnership($appointment, $request->user()->doctor);

        if ($session->status !== 'active') {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => 'Only active sessions can be ended.',
                'errorCode' => '422',
            ], 422);
        }

        DB::transaction(function () use ($request, $appointment, $session) {
            $session->update([
                'status' => 'completed',
                'ended_at' => now(),
                'diagnosis' => $request->diagnosis ?? null,
                'notes' => $request->notes,
            ]);

            $appointment->update([
                'status' => AppointmentStatus::Completed,
            ]);
        });

        if ($appointment->patient?->user) {
            $appointment->patient->user->notify(new SessionStatusNotification($session->fresh()));
        }

        return response()->json([
            'success' => true,
            'data' => ['session' => $session->fresh('prescriptions')],
            'error' => null,
            'errorCode' => null,
        ]);
    }

    public function show(Request $request, Appointment $appointment, AppointmentSession $session)
    {
        $this->checkOwnership($appointment, $request->user()->doctor);

        $session->load('prescriptions');

        return response()->json([
            'success' => true,
            'data' => ['session' => $session],
            'error' => null,
            'errorCode' => null,
        ]);
    }
}

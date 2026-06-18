<?php

namespace App\Http\Controllers\Receptionist;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reception\StoreAppointmentRequest;
use App\Http\Requests\Reception\RescheduleAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Doctor;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentService $appointments)
    {
    }

    //  view and filter appointments
    public function index(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d',
            'doctor_id' => 'nullable|exists:doctors,id',
            'status' => 'nullable|string',
            'search' => 'nullable|string',
        ]);

        $query = Appointment::applyFilters($request->only([
            'date', 'date_from', 'date_to', 'doctor_id', 'status', 'search'
        ]));

        // default date is today if there is no date in the request
        if (!$request->filled(['date', 'date_from', 'date_to'])) {
            $query->whereDate('scheduled_at', \Carbon\Carbon::today());
        }

        $appointments = $query->with(['patient', 'doctor'])
            ->orderBy('scheduled_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => AppointmentResource::collection($appointments)
        ]);
    }


    public function store(StoreAppointmentRequest $request)
    {
        $validated = $request->validated();
        $scheduledAt = Carbon::parse($validated['scheduled_at']);

        return DB::transaction(function () use ($validated, $scheduledAt) {

            //  Lock the doctor's schedule so no one else can book them right now
            $doctor = Doctor::where('id', $validated['doctor_id'])->lockForUpdate()->firstOrFail();

            if ($this->slots->hasConflict($doctor->id, $scheduledAt)) {
                return response()->json([
                    'success' => false,
                    'error' => 'This time slot is already booked for the selected doctor.'
                ], 422);
            }

            $appointment = Appointment::create([
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $doctor->id,
                'scheduled_at' => $scheduledAt,
                'status' => AppointmentStatus::Arrived, // As requested in your original code
                'visit_reason' => $validated['visit_reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully',
                'data' => new AppointmentResource($appointment)
            ], 201);
        });
    }


    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor']);
        return response()->json(['success' => true, 'data' => new AppointmentResource($appointment)]);
    }

    public function confirm(Appointment $appointment)
    {
        if ($appointment->status !== AppointmentStatus::Scheduled) {
            return response()->json(['success' => false, 'error' => 'Only scheduled appointments can be confirmed.'], 422);
        }

        $appointment->update(['status' => AppointmentStatus::Confirmed]);
        return response()->json(['success' => true, 'message' => 'Appointment confirmed.', 'data' => new AppointmentResource($appointment)]);
    }

    public function markArrived(Appointment $appointment)
    {
        if (!in_array($appointment->status, [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed])) {
            return response()->json(['success' => false, 'error' => 'Only scheduled or confirmed appointments can be marked arrived.'], 422);
        }

        $appointment->update(['status' => AppointmentStatus::Arrived]);
        return response()->json(['success' => true, 'message' => 'Patient marked as arrived.', 'data' => new AppointmentResource($appointment)]);
    }

    public function reschedule(RescheduleAppointmentRequest $request, Appointment $appointment)
    {
        $this->appointments->reschedule(
            $appointment,
            Carbon::parse($request->validated('scheduled_at'))
        );

        return response()->json([
            'success' => true,
            'message' => 'Appointment rescheduled.',
            'data' => new AppointmentResource($appointment->fresh()),
        ]);
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        $validated = $request->validate(['reason' => 'nullable|string|max:255']);

        $this->appointments->cancel($appointment, 'receptionist', $validated['reason'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled.',
            'data' => new AppointmentResource($appointment),
        ]);
    }


    public function markNoShow(Appointment $appointment)
    {
        $this->appointments->markAsNoShow($appointment);

        return response()->json([
            'success' => true,
            'message' => 'Marked as no-show.',
            'data' => new AppointmentResource($appointment),
        ]);
    }
}

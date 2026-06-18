<?php

namespace App\Http\Controllers\Patient;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentService $appointments)
    {
    }

    // 1. Book a new appointment


    public function store(StoreAppointmentRequest $request)
    {
        $validated = $request->validated();
        $scheduledAt = Carbon::parse($validated['scheduled_at']);

        return DB::transaction(function () use ($validated, $scheduledAt, $request) {

            // Lock the doctor's schedule so no one else can book them right now
            $doctor = Doctor::where('id', $validated['doctor_id'])->lockForUpdate()->firstOrFail();

            // Check for conflicts safely inside the lock
            if ($this->slots->hasConflict($doctor->id, $scheduledAt)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Sorry, this time slot is already booked. Please choose another time.',
                ], 422);
            }

            // 3. Create the appointment
            $appointment = Appointment::create([
                'patient_id' => $request->user()->patient->id,
                'doctor_id' => $doctor->id,
                'scheduled_at' => $scheduledAt,
                'status' => AppointmentStatus::tryFrom(config('appointments.initial_status')),
                'visit_reason' => $validated['visit_reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $appointment->load(['patient', 'doctor']);

            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully!',
                'data' => new AppointmentResource($appointment),
            ], 201);
        });
    }


    // my appointments with filters
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|string',
            'doctor_id' => 'nullable|exists:doctors,id',
            'timeline' => 'nullable|in:upcoming,past',
        ]);

        $sortOrder = $request->timeline === 'upcoming' ? 'asc' : 'desc';

        $appointments = Appointment::where('patient_id', $request->user()->patient->id)
            ->applyFilters($request->only(['status', 'doctor_id', 'timeline']))
            ->with(['doctor', 'doctor.department'])
            ->orderBy('scheduled_at', $sortOrder)
            ->get();

        return response()->json([
            'success' => true,
            'data' => AppointmentResource::collection($appointments),
        ]);
    }

    // get available slots for a doctor on a given date
    public function availableSlots(Request $request)
    {
        // git it from the appointments service
        $slots = $this->appointments->availableSlots(
            (int)$request->input('doctor_id'),
            Carbon::parse($request->input('date'))
        );

        return response()->json(['success' => true, 'data' => $slots]);
    }

    // cancel my appointment
    public function cancel(Request $request, Appointment $appointment)
    {
        abort_if($appointment->patient_id !== $request->user()->patient->id, 403, 'Not your appointment.');

        $validated = $request->validate(['reason' => 'nullable|string|max:255']);

        // DomainException is caught globally → 422 JSON automatically
        $this->appointments->cancel($appointment, 'patient', $validated['reason'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled.',
            'data' => new AppointmentResource($appointment),
        ]);
    }
}

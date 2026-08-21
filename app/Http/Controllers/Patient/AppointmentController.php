<?php

namespace App\Http\Controllers\Patient;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StoreAppointmentRequest;
use App\Http\Requests\Reception\RescheduleAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Throwable;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentService $appointments) {}

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
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => AppointmentResource::collection($appointments->items()),
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(StoreAppointmentRequest $request)
    {
        $validated = $request->validated();
        $scheduledAt = Carbon::parse($validated['scheduled_at']);

        $appointment = $this->appointments->createAppointment(
            patientId: $request->user()->patient->id,
            doctorId: $validated['doctor_id'],
            scheduledAt: $scheduledAt,
            status: AppointmentStatus::Scheduled,
            visitReason: $validated['visit_reason'] ?? null,
            notes: $validated['notes'] ?? null,
        );

        $appointment->load(['patient', 'doctor']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully!',
            'data' => new AppointmentResource($appointment),
        ], 201);
    }

    public function show(Appointment $appointment)
    {
        abort_if(
            $appointment->patient_id !== auth()->user()->patient->id,
            403,
            'Not your appointment.'
        );
        $appointment->load(['patient', 'doctor', 'doctor.department', 'sessions']);

        return response()->json([
            'success' => true,
            'data' => new AppointmentResource($appointment),
        ]);
    }

    public function medicalRecord(Request $request)
    {
        $patient = $request->user()->patient;
        $appointments = $this->appointments->medicalRecord($patient, perPage: 10);

        return response()->json([
            'success' => true,
            'data' => [
                'patient' => $patient->only([
                    'id', 'full_name', 'phone', 'date_of_birth', 'gender',
                ]),
                'appointments' => AppointmentResource::collection($appointments->items()),
            ],
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ],
            'error' => null,
            'errorCode' => null,
        ]);
    }

    // get available slots for a doctor on a given date
    public function availableSlots(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date_format:Y-m-d',
        ]);

        // git it from the appointments service
        $slots = $this->appointments->availableSlots(
            (int) $request->input('doctor_id'),
            Carbon::parse($request->input('date'))
        );

        return response()->json(['success' => true, 'data' => $slots]);
    }

    public function availableDays(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        $availableDays = $this->appointments->getAvailableDays(
            (int) $request->input('doctor_id')
        );

        return response()->json(['success' => true, 'data' => $availableDays]);
    }

    // cancel my appointment
    public function cancel(Request $request, Appointment $appointment)
    {
        abort_if(
            $appointment->patient_id !== $request->user()->patient->id,
            403,
            'Not your appointment.'
        );

        $validated = $request->validate(['reason' => 'nullable|string|max:255']);

        $this->appointments->cancel($appointment, 'patient', $validated['reason'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled.',
            'data' => new AppointmentResource($appointment),
        ]);
    }

    public function reschedule(RescheduleAppointmentRequest $request, Appointment $appointment)
    {
        abort_if(
            $appointment->patient_id !== $request->user()->patient->id,
            403,
            'Not your appointment.'
        );

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
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reception\RescheduleAppointmentRequest;
use App\Http\Requests\Reception\StoreAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Patient;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments) {}

    public function index(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
            'doctor_id' => 'nullable|exists:doctors,id',
            'status' => 'nullable|string',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $query = Appointment::applyFilters($request->only([
            'date', 'date_from', 'date_to', 'doctor_id', 'status', 'search',
        ]));

        $perPage = $request->input('per_page', 15); // Default to 15 items per page

        $appointments = $query->with(['patient', 'doctor', 'doctor.department'])
            ->orderBy('scheduled_at', 'desc') // Order by latest
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => AppointmentResource::collection($appointments),
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
            patientId: $validated['patient_id'],
            doctorId: $validated['doctor_id'],
            scheduledAt: $scheduledAt,
            status: AppointmentStatus::Scheduled,
            visitReason: $validated['visit_reason'] ?? null,
            notes: $validated['notes'] ?? null,
        );
        $appointment->load(['patient', 'doctor']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully',
            'data' => new AppointmentResource($appointment),
        ], 201);
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor', 'doctor.department', 'sessions']);

        return response()->json([
            'success' => true,
            'data' => new AppointmentResource($appointment),
        ]);
    }

    public function medicalRecord(Patient $patient)
    {
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

    public function confirm(Appointment $appointment)
    {
        if ($appointment->status !== AppointmentStatus::Scheduled) {
            return response()->json(['success' => false, 'error' => 'Only scheduled appointments can be confirmed.'], 422);
        }

        $appointment->update(['status' => AppointmentStatus::Confirmed]);

        return response()->json(['success' => true, 'message' => 'Appointment confirmed.', 'data' => new AppointmentResource($appointment)]);
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        $validated = $request->validate(['reason' => 'nullable|string|max:255']);

        $this->appointments->cancel($appointment, 'admin', $validated['reason'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled.',
            'data' => new AppointmentResource($appointment),
        ]);
    }

    public function availableSlots(Request $request)
    {
        // get it from the appointments service
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

    public function markNoShow(Appointment $appointment)
    {
        $this->appointments->markAsNoShow($appointment);

        return response()->json([
            'success' => true,
            'message' => 'Marked as no-show.',
            'data' => new AppointmentResource($appointment),
        ]);
    }

    public function stats(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
            'doctor_id' => 'nullable|exists:doctors,id',
        ]);

        $query = Appointment::query()
            ->when($request->filled('doctor_id'), fn ($q) => $q->where('doctor_id', $request->doctor_id))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('scheduled_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('scheduled_at', '<=', $request->date_to));

        // Default to today if no range given
        if (! $request->hasAny(['date_from', 'date_to'])) {
            $query->whereDate('scheduled_at', Carbon::today());
        }

        $statusCounts = $query->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $total = array_sum($statusCounts);

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'scheduled' => $statusCounts[AppointmentStatus::Scheduled->value] ?? 0,
                'confirmed' => $statusCounts[AppointmentStatus::Confirmed->value] ?? 0,
                'arrived' => $statusCounts[AppointmentStatus::Arrived->value] ?? 0,
                'in_progress' => $statusCounts[AppointmentStatus::InProgress->value] ?? 0,
                'completed' => $statusCounts[AppointmentStatus::Completed->value] ?? 0,
                'cancelled' => $statusCounts[AppointmentStatus::Canceled->value] ?? 0,
                'no_show' => $statusCounts[AppointmentStatus::NoShow->value] ?? 0,
                'completion_rate' => $total > 0
                    ? round(($statusCounts[AppointmentStatus::Completed->value] ?? 0) / $total, 2)
                    : 0,
            ],
        ]);
    }

    public function markArrived(Appointment $appointment)
    {
        if (! in_array($appointment->status, [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed])) {
            return response()->json(['success' => false, 'error' => 'Only scheduled or confirmed appointments can be marked arrived.'], 422);
        }

        $appointment->update(['status' => AppointmentStatus::Arrived]);

        return response()->json(['success' => true, 'message' => 'Patient marked as arrived.', 'data' => new AppointmentResource($appointment)]);
    }
}

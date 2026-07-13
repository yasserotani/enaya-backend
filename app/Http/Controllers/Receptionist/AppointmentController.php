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
use Throwable;

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
     * @throws  Throwable
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

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully',
            'data' => new AppointmentResource($appointment)
        ], 201);
    }


    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor', 'doctor.department', 'sessions']);
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

    public function availableSlots(Request $request)
    {
        // get it from the appointments service
        $slots = $this->appointments->availableSlots(
            (int)$request->input('doctor_id'),
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
            (int)$request->input('doctor_id')
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

    public function markArrived(Appointment $appointment)
    {
        if (!in_array($appointment->status, [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed])) {
            return response()->json(['success' => false, 'error' => 'Only scheduled or confirmed appointments can be marked arrived.'], 422);
        }

        $appointment->update(['status' => AppointmentStatus::Arrived]);
        return response()->json(['success' => true, 'message' => 'Patient marked as arrived.', 'data' => new AppointmentResource($appointment)]);
    }
}

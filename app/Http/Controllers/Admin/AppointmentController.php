<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reception\RescheduleAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{

    public function __construct(private AppointmentService $appointments)
    {
    }


    // Full clinic-wide view with every filter available
    public function index(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date_format:Y-m-d',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
            'doctor_id' => 'nullable|exists:doctors,id',
            'status' => 'nullable|string',
            'search' => 'nullable|string|max:100',
        ]);

        $query = Appointment::applyFilters($request->only([
            'date', 'date_from', 'date_to', 'doctor_id', 'status', 'search',
        ]));

        // Default to today when no date filter is provided
        if (!$request->hasAny(['date', 'date_from', 'date_to'])) {
            $query->whereDate('scheduled_at', Carbon::today());
        }

        $appointments = $query->with(['patient', 'doctor', 'doctor.department'])
            ->orderBy('scheduled_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => AppointmentResource::collection($appointments),
        ]);
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor', 'doctor.department', 'sessions']);

        return response()->json([
            'success' => true,
            'data' => new AppointmentResource($appointment),
        ]);
    }

    // Admin can cancel any appointment regardless of who owns it
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

    public function markNoShow(Appointment $appointment)
    {
        $this->appointments->markAsNoShow($appointment);

        return response()->json([
            'success' => true,
            'message' => 'Marked as no-show.',
            'data' => new AppointmentResource($appointment),
        ]);
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


    public function stats(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d|after_or_equal:date_from',
            'doctor_id' => 'nullable|exists:doctors,id',
        ]);

        $query = Appointment::query()
            ->when($request->filled('doctor_id'), fn($q) => $q->where('doctor_id', $request->doctor_id))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('scheduled_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('scheduled_at', '<=', $request->date_to));

        // Default to today if no range given
        if (!$request->hasAny(['date_from', 'date_to'])) {
            $query->whereDate('scheduled_at', Carbon::today());
        }

        $appointments = $query->get();
        $total = $appointments->count();

        $byStatus = $appointments
            ->groupBy(fn($a) => $a->status->value)
            ->map->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'scheduled' => $byStatus[AppointmentStatus::Scheduled->value] ?? 0,
                'confirmed' => $byStatus[AppointmentStatus::Confirmed->value] ?? 0,
                'arrived' => $byStatus[AppointmentStatus::Arrived->value] ?? 0,
                'in_progress' => $byStatus[AppointmentStatus::InProgress->value] ?? 0,
                'completed' => $byStatus[AppointmentStatus::Completed->value] ?? 0,
                'cancelled' => $byStatus[AppointmentStatus::Canceled->value] ?? 0,
                'no_show' => $byStatus[AppointmentStatus::NoShow->value] ?? 0,
                'completion_rate' => $total > 0
                    ? round(($byStatus[AppointmentStatus::Completed->value] ?? 0) / $total, 2)
                    : 0,
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Doctor;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments) {}

    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|string',
            'date' => 'nullable|date_format:Y-m-d',
            'timeline' => 'nullable|in:upcoming,past',
        ]);

        $filters = $request->only(['status', 'date', 'timeline']);

        // today is the default date
        if (empty($filters['date']) && empty($filters['timeline'])) {
            $filters['date'] = now()->toDateString();
        }

        $query = Appointment::where('doctor_id', $request->user()->doctor->id)
            ->applyFilters($filters);

        // show closest appointments first, unless looking at past history
        $sortOrder = (isset($filters['timeline']) && $filters['timeline'] === 'past') ? 'desc' : 'asc';

        $appointments = $query->with('patient')
            ->orderBy('scheduled_at', $sortOrder)
            ->get();

        return response()->json([
            'success' => true,
            'data' => AppointmentResource::collection($appointments),
        ]);
    }

    public function show(Request $request, Appointment $appointment)
    {
        abort_if($appointment->doctor_id !== $request->user()->doctor->id, 403, 'Not your appointment.');

        $appointment->load(['patient', 'doctor']);

        return response()->json([
            'success' => true,
            'data' => new AppointmentResource($appointment),
        ]);
    }

    public function confirm(Appointment $appointment)
    {
        abort_if(
            $appointment->doctor_id !== auth()->user()->doctor->id,
            403,
            'Not your appointment.'
        );

        if ($appointment->status !== AppointmentStatus::Scheduled) {
            return response()->json(['success' => false, 'error' => 'Only scheduled appointments can be confirmed.'], 422);
        }

        $appointment->update(['status' => AppointmentStatus::Confirmed]);

        return response()->json(['success' => true, 'message' => 'Appointment confirmed.', 'data' => new AppointmentResource($appointment)]);
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        abort_if(
            $appointment->doctor_id !== $request->user()->doctor->id,
            403,
            'Not your appointment.'
        );

        $this->appointments->cancel($appointment, 'doctor', null);

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled.',
            'data' => new AppointmentResource($appointment),
        ]);
    }

    public function markNoShow(Request $request, Appointment $appointment)
    {
        abort_if($appointment->doctor_id !== $request->user()->doctor->id,
            403, 'Not your appointment.');

        $this->appointments->markAsNoShow($appointment);

        return response()->json([
            'success' => true,
            'message' => 'Marked as no-show.',
            'data' => new AppointmentResource($appointment),
        ]);
    }

    public function availableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $doctorId = $request->user()->doctor->id;
        $date = Carbon::parse($request->input('date'));

        $slots = $this->appointments->availableSlots($doctorId, $date);

        return response()->json([
            'success' => true,
            'data' => $slots,
        ]);
    }

    public function availableDays(Request $request)
    {
        $doctorId = $request->user()->doctor->id;
        $availableDays = $this->appointments->getAvailableDays($doctorId);

        return response()->json(['success' => true, 'data' => $availableDays]);
    }
}

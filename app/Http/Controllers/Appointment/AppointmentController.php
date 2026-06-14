<?php

namespace App\Http\Controllers\Appointment;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Enums\AppointmentStatus;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    // 1. Book a new appointment
    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'scheduled_at' => 'required|date_format:Y-m-d H:i:s|after:now',
            'visit_reason' => 'nullable|string',
        ]);

        $requestedTime = Carbon::parse($validated['scheduled_at']);
        $doctorId = $validated['doctor_id'];

        // Check for conflicts
        $isConflict = Appointment::where('doctor_id', $doctorId)
            ->whereIn('status', [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed])
            ->where('scheduled_at', '>', $requestedTime->copy()->subMinutes(30))
            ->where('scheduled_at', '<', $requestedTime->copy()->addMinutes(30))
            ->exists();

        if ($isConflict) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, this time slot is already booked. Please choose another time.'
            ], 422);
        }

        $patientId = $request->user()->patient->id;

        $appointment = Appointment::create([
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'scheduled_at' => $requestedTime,
            'status' => AppointmentStatus::Scheduled,
            'visit_reason' => $validated['visit_reason'] ?? null,

        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully!',
            'data' => $appointment
        ], 201);
    }

    // 2. My appointments with a specific doctor
    public function getMyAppointmentsWithDoctor(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id'
        ]);

        $patientId = $request->user()->patient->id;
        $doctorId = $request->input('doctor_id');

        $appointments = Appointment::where('patient_id', $patientId)
            ->where('doctor_id', $doctorId)
            ->whereIn('status', [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed])
            ->with('doctor.user')
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }

    // 3. All my appointments (with any doctor)
    public function index(Request $request)
    {
        $patientId = $request->user()->patient->id;

        $appointments = Appointment::where('patient_id', $patientId)
            ->with(['doctor.user', 'doctor.department']) // Fixed to 'doctor.department' instead of just 'department'
            ->orderBy('scheduled_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }

    public function getDoctorAvailableSlots(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today', // Ensure date format is correct and not in the past
        ]);

        $doctorId = $request->input('doctor_id');
        $date = Carbon::parse($request->input('date'));

        $availableSlots = $this->generateAvailableSlots($doctorId, $date);

        return response()->json([
            'success' => true,
            'data' => $availableSlots
        ]);
    }

    // --- Helper Method to Generate Slots ---
    private function generateAvailableSlots(int $doctorId, Carbon $date)
    {
        $startHour = 8; // Clinic opens at 08:00 AM
        $endHour = 16;  // Clinic closes at 04:00 PM
        $slotDuration = 30; // 30 minutes per appointment

        // Fetch booked appointments for this doctor on the selected day
        $bookedAppointments = Appointment::where('doctor_id', $doctorId)
            ->whereIn('status', [AppointmentStatus::Scheduled, AppointmentStatus::Confirmed])
            ->whereDate('scheduled_at', $date->toDateString())
            ->get();

        $slots = [];
        $startTime = $date->copy()->startOfDay()->addHours($startHour);
        $endTime = $date->copy()->startOfDay()->addHours($endHour);

        while ($startTime->lessThan($endTime)) {
            $currentSlot = $startTime->copy();

            // Check if the current 30-min slot conflicts with any booked appointment
            $isBooked = $bookedAppointments->contains(function ($appointment) use ($currentSlot, $slotDuration) {
                $appointmentTime = Carbon::parse($appointment->scheduled_at);
                return $appointmentTime->greaterThan($currentSlot->copy()->subMinutes($slotDuration)) &&
                    $appointmentTime->lessThan($currentSlot->copy()->addMinutes($slotDuration));
            });

            // Add to available slots if it's not booked AND the time hasn't passed yet
            if (!$isBooked && $currentSlot->isAfter(Carbon::now())) {
                $slots[] = $currentSlot->format('Y-m-d H:i:s');
            }

            $startTime->addMinutes($slotDuration);
        }

        return $slots;
    }
}

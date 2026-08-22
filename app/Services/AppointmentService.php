<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Notifications\AppointmentCancelledNotification;
use App\Notifications\AppointmentNoShowNotification;
use App\Notifications\AppointmentRescheduledNotification;
use App\Notifications\NewAppointmentNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;

class AppointmentService
{
    private const int SLOT_MINUTES = 30;

    private const array ACTIVE_STATUSES = [
        AppointmentStatus::Scheduled,
        AppointmentStatus::Confirmed,
        AppointmentStatus::Arrived,
    ];

    /**
     * Convert ACTIVE_STATUSES enum cases to their string values for queries.
     */
    private function activeStatusValues(): array
    {
        return array_map(fn ($s) => $s->value, self::ACTIVE_STATUSES);
    }

    // Slot actions
    public function availableSlots(int $doctorId, Carbon $date): array
    {
        $doctor = Doctor::findOrFail($doctorId);

        // Keep calendar date stable and normalize it to the app timezone.
        $appTz = config('app.timezone') ?: date_default_timezone_get();
        $date = Carbon::createFromFormat('Y-m-d', $date->toDateString(), $appTz)->startOfDay();
        $now = now($appTz);

        // Ensure working hours are treated as time-only (ignore any stored date portion)
        $startTime = Carbon::parse($doctor->working_hours_start)->format('H:i');
        $endTime = Carbon::parse($doctor->working_hours_end)->format('H:i');

        $cursor = $date->copy()->setTimeFromTimeString($startTime);
        $end = $date->copy()->setTimeFromTimeString($endTime);

        // Fetch booked slots for the given date (normalize booked times to app timezone)
        $booked = Appointment::where('doctor_id', $doctorId)
            ->whereIn('status', $this->activeStatusValues())
            ->whereDate('scheduled_at', $date->toDateString())
            ->pluck('scheduled_at')
            ->map(fn ($t) => Carbon::parse($t)->setTimezone($appTz)->format('Y-m-d H:i:s'));

        $slots = [];
        while ($cursor->lessThan($end)) {
            // Only filter out past times when the requested date is today.
            //            $isFuture = $date->isSameDay(now()) ? $cursor->isAfter(now()) : true;
            $isFuture = true;
            if (! $booked->contains($cursor->format('Y-m-d H:i:s')) && $isFuture) {
                $slots[] = $cursor->format('Y-m-d H:i:s');
            }

            $cursor->addMinutes(self::SLOT_MINUTES);
        }

        return $slots;
    }

    public function getAvailableDays(int $doctorId): array
    {
        $days = [];

        $cursor = now()->copy()->startOfDay();
        $end = $cursor->copy()->addMonth();

        while ($cursor->lessThan($end)) {
            if (! empty($this->availableSlots($doctorId, $cursor->copy()))) {
                $days[] = $cursor->toDateString();
            }
            $cursor->addDay();
        }

        return $days;
    }

    public function hasConflict(int $doctorId, Carbon $time, ?int $excludeAppointmentId = null): bool
    {
        return Appointment::where('doctor_id', $doctorId)
            ->whereIn('status', $this->activeStatusValues())
            ->when($excludeAppointmentId, fn ($q) => $q->where('id', '!=', $excludeAppointmentId))
            ->whereBetween('scheduled_at', [
                $time->copy()->subMinutes(self::SLOT_MINUTES - 1),
                $time->copy()->addMinutes(self::SLOT_MINUTES - 1),
            ])
            ->exists();
    }

    public function medicalRecord(Patient $patient, ?int $doctorId = null, int $perPage = 10): LengthAwarePaginator
    {
        return Appointment::query()
            ->whereBelongsTo($patient)
            ->when($doctorId !== null, fn ($query) => $query->where('doctor_id', $doctorId))
            ->with([
                'doctor:id,user_id,full_name,specialty',
                'sessions',
                'sessions.prescriptions',
            ])
            ->orderByDesc('scheduled_at')
            ->paginate($perPage);
    }

    //  Appointment Actions

    /**
     * @throws Throwable
     */
    public function createAppointment(
        int $patientId,
        int $doctorId,
        Carbon $scheduledAt,
        AppointmentStatus $status,
        ?string $visitReason = null,
        ?string $notes = null
    ): Appointment {
        return DB::transaction(function () use ($patientId, $doctorId, $scheduledAt, $status, $visitReason, $notes) {

            // get and lock the doctor record while updating
            $doctor = Doctor::where('id', $doctorId)->lockForUpdate()->firstOrFail();

            if ($this->hasConflict($doctor->id, $scheduledAt)) {
                throw new \DomainException('This time slot is already booked for the selected doctor.');
            }

            $appointment = Appointment::create([
                'patient_id' => $patientId,
                'doctor_id' => $doctor->id,
                'scheduled_at' => $scheduledAt,
                'status' => $status,
                'visit_reason' => $visitReason,
                'notes' => $notes,
            ]);

            $patient = Patient::findOrFail($patientId);
            if ($doctor->user) {
                $doctor->user->notify(new NewAppointmentNotification($appointment));
            }

            if ($patient->user) {
                $patient->user->notify(new NewAppointmentNotification($appointment));
            }

            return $appointment;
        });
    }

    public function cancel(Appointment $appointment, string $cancelledBy, ?string $reason): Appointment
    {
        if (! in_array($appointment->status, [
            AppointmentStatus::Scheduled,
            AppointmentStatus::Confirmed,
            AppointmentStatus::Arrived,
        ])) {
            throw new \DomainException('This appointment can no longer be cancelled.');
        }

        $appointment->update([
            'status' => AppointmentStatus::Canceled,
            'cancelled_by' => $cancelledBy,
        ]);
        // notify whoever did NOT cancel it
        if ($cancelledBy === 'patient') {
            if ($appointment->doctor?->user) {
                $appointment->doctor->user->notify(new AppointmentCancelledNotification($appointment));
            }
        } else {
            if ($appointment->patient?->user) {
                $appointment->patient->user->notify(new AppointmentCancelledNotification($appointment));
            }
        }

        return $appointment;
    }

    public function markAsNoShow(Appointment $appointment): Appointment
    {
        if (! in_array($appointment->status, [
            AppointmentStatus::Scheduled,
            AppointmentStatus::Confirmed,
        ])) {
            throw new \DomainException('Only scheduled or confirmed appointments can be marked as no-show.');
        }

        $appointment->update(['status' => AppointmentStatus::NoShow]);

        if ($appointment->doctor?->user) {
            $appointment->doctor->user->notify(new AppointmentNoShowNotification($appointment));
        }

        return $appointment;
    }

    public function reschedule(Appointment $appointment, Carbon $newTime): Appointment
    {
        if (in_array($appointment->status, [
            AppointmentStatus::Completed,
            AppointmentStatus::Canceled,
        ])) {
            throw new \DomainException('This appointment can no longer be rescheduled.');
        }

        if ($this->hasConflict($appointment->doctor_id, $newTime, $appointment->id)) {
            throw new \DomainException('The new time slot is already booked.');
        }

        $appointment->update([
            'scheduled_at' => $newTime,
            'status' => AppointmentStatus::Scheduled,
        ]);

        if ($appointment->patient?->user) {
            $appointment->patient->user->notify(new AppointmentRescheduledNotification($appointment));
        }

        if ($appointment->doctor?->user) {
            $appointment->doctor->user->notify(new AppointmentRescheduledNotification($appointment));
        }

        return $appointment;
    }
}

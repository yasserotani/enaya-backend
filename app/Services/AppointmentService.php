<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
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


    // Slot actions
    public function availableSlots(int $doctorId, Carbon $date): array
    {
        $doctor = Doctor::findOrFail($doctorId);

        $cursor = $date->copy()->setTimeFromTimeString($doctor->working_hours_start);
        $end = $date->copy()->setTimeFromTimeString($doctor->working_hours_end);

        $booked = Appointment::where('doctor_id', $doctorId)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->whereDate('scheduled_at', $date->toDateString())
            ->pluck('scheduled_at')
            ->map(fn($t) => Carbon::parse($t)->format('Y-m-d H:i:s'));

        $slots = [];
        while ($cursor->lessThan($end)) {
            if (!$booked->contains($cursor->format('Y-m-d H:i:s')) && $cursor->isAfter(now())) {
                $slots[] = $cursor->format('Y-m-d H:i:s');
            }
            $cursor->addMinutes(self::SLOT_MINUTES);
        }

        return $slots;
    }

    public function hasConflict(int $doctorId, Carbon $time, ?int $excludeAppointmentId = null): bool
    {
        return Appointment::where('doctor_id', $doctorId)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->when($excludeAppointmentId, fn($q) => $q->where('id', '!=', $excludeAppointmentId))
            ->whereBetween('scheduled_at', [
                $time->copy()->subMinutes(self::SLOT_MINUTES - 1),
                $time->copy()->addMinutes(self::SLOT_MINUTES - 1),
            ])
            ->exists();
    }


    //  Appointment Actions

    /**
     * @throws Throwable
     */
    public function createAppointment(
        int               $patientId,
        int               $doctorId,
        Carbon            $scheduledAt,
        AppointmentStatus $status,
        ?string           $visitReason = null,
        ?string           $notes = null
    ): Appointment
    {
        return DB::transaction(function () use ($patientId, $doctorId, $scheduledAt, $status, $visitReason, $notes) {

            // get and lock the doctor record while updating 
            $doctor = Doctor::where('id', $doctorId)->lockForUpdate()->firstOrFail();

            if ($this->hasConflict($doctor->id, $scheduledAt)) {
                throw new \DomainException('This time slot is already booked for the selected doctor.');
            }

            return Appointment::create([
                'patient_id' => $patientId,
                'doctor_id' => $doctor->id,
                'scheduled_at' => $scheduledAt,
                'status' => $status,
                'visit_reason' => $visitReason,
                'notes' => $notes,
            ]);
        });
    }


    public function cancel(Appointment $appointment, string $cancelledBy, ?string $reason): Appointment
    {
        if (!in_array($appointment->status, [
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

        return $appointment;
    }

    public function markAsNoShow(Appointment $appointment): Appointment
    {
        if (!in_array($appointment->status, [
            AppointmentStatus::Scheduled,
            AppointmentStatus::Confirmed,
        ])) {
            throw new \DomainException('Only scheduled or confirmed appointments can be marked as no-show.');
        }

        $appointment->update(['status' => AppointmentStatus::NoShow]);

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

        return $appointment;
    }


}

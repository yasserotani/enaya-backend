<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['doctor_id', 'patient_id', 'scheduled_at', 'status', 'visit_reason', 'notes', 'cancelled_by'])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    public function scopeApplyFilters($query, array $filters)
    {
        // 1. Filter by Doctor
        if (! empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        // 2. Filter by Status
        if (! empty($filters['status'])) {
            $statusEnum = AppointmentStatus::tryFrom($filters['status']);
            $query->where('status', $statusEnum);
        }

        // 3. Filter by a Single Specific Date
        if (! empty($filters['date'])) {
            $query->whereDate('scheduled_at', $filters['date']);
        }

        // 4. Filter by Date Ranges (e.g., view this week's appointments)
        if (! empty($filters['date_from'])) {
            $query->whereDate('scheduled_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('scheduled_at', '<=', $filters['date_to']);
        }

        // 5. Smart Search (Searches patient info OR visit reasons)
        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                // Search inside the appointment's own reason or notes
                $q->where('visit_reason', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    // Look inside the connected Patient relationship
                    ->orWhereHas('patient', function ($patientQuery) use ($search) {
                        $patientQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }
        // coming vs past appointments for the pateint
        if (! empty($filters['timeline'])) {
            if ($filters['timeline'] === 'upcoming') {
                $query->where('scheduled_at', '>=', now());
            } elseif ($filters['timeline'] === 'past') {
                $query->where('scheduled_at', '<', now());
            }
        }

        return $query;
    }

    public function doctor()
    {
        // This ensures that anytime you load an appointment's doctor,
        // it never returns null just because the doctor left the clinic.
        return $this->belongsTo(Doctor::class)->withTrashed();
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function sessions(): HasOne
    {
        return $this->hasOne(AppointmentSession::class);
    }

    public function prescriptions(): HasManyThrough
    {
        return $this->hasManyThrough(Prescription::class, AppointmentSession::class);
    }

    protected function casts(): array
    {
        return [
            'doctor_id' => 'integer',
            'patient_id' => 'integer',
            'scheduled_at' => 'datetime',
            'status' => AppointmentStatus::class,
        ];
    }
}

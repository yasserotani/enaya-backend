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

#[Fillable(['doctor_id', 'patient_id', 'scheduled_at', 'status', 'visit_reason', 'notes'])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function queue(): HasOne
    {
        return $this->hasOne(Queue::class);
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

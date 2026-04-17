<?php

namespace App\Models;

use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['doctor_id', 'patient_id', 'scheduled_at', 'status', 'notes'])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'doctor_id' => 'integer',
            'patient_id' => 'integer',
            'scheduled_at' => 'datetime',
        ];
    }

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
}

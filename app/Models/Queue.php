<?php

namespace App\Models;

use Database\Factories\QueueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['doctor_id', 'patient_id', 'appointment_id', 'position', 'status', 'notes', 'waiting_since'])]
class Queue extends Model
{
    /** @use HasFactory<QueueFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'doctor_id' => 'integer',
            'patient_id' => 'integer',
            'appointment_id' => 'integer',
            'position' => 'integer',
            'waiting_since' => 'datetime',
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

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}

<?php

namespace App\Models;

use Database\Factories\AppointmentSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['appointment_id', 'started_at', 'ended_at', 'notes', 'patient_complaint', 'diagnosis', 'status'])]
class AppointmentSession extends Model
{
    /** @use HasFactory<AppointmentSessionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'appointment_id' => 'integer',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}

<?php

namespace App\Models;

use Database\Factories\PrescriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['appointment_session_id', 'medication_name', 'dosage', 'frequency', 'duration', 'notes'])]
class Prescription extends Model
{
    /** @use HasFactory<PrescriptionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'appointment_session_id' => 'integer',
            'duration' => 'integer',
        ];
    }

    public function appointmentSession(): BelongsTo
    {
        return $this->belongsTo(AppointmentSession::class);
    }
}

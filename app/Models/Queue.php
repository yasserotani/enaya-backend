<?php

namespace App\Models;

use Database\Factories\QueueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['doctor_id', 'patient_id', 'appointment_id', 'position', 'status', 'notes'])]
class Queue extends Model
{
    /** @use HasFactory<QueueFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'doctor_id' => 'integer',
            'patient_id' => 'integer',
            'appointment_id' => 'integer',
            'position' => 'integer',
        ];
    }
}

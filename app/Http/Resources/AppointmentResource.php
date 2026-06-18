<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'scheduled_at' => $this->scheduled_at,
            'status' => $this->status,
            'visit_reason' => $this->visit_reason,
            'notes' => $this->notes,
            'created_at' => $this->created_at,

            // Access the relationships if they are loaded
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'patient' => new PatientResource($this->whenLoaded('patient')),
            'session' => new AppointmentSessionResource($this->whenLoaded('sessions')), // Corrected to 'sessions' and 'new'
        ];
    }
}

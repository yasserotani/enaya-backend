<?php


namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'doctor' => new DoctorResource($this->whenLoaded('doctor')),
            'patient' => new PatientResource($this->whenLoaded('patient')),
            'session' => new AppointmentSessionResource($this->whenLoaded('sessions')), // Changed to 'sessions' to match the relationship name
            'scheduled_at' => $this->scheduled_at?->format('Y-m-d H:i:s'),
            'status' => $this->status->value,
            'visit_reason' => $this->visit_reason,
            'notes' => $this->notes,
            'cancelled_by' => $this->cancelled_by,
            'created_at' => $this->created_at,
        ];
    }
}

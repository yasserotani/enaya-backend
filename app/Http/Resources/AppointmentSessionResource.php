<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentSessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'started_at' => $this->started_at?->format('Y-m-d H:i:s'),
            'ended_at' => $this->ended_at?->format('Y-m-d H:i:s'),
            'notes' => $this->notes,
            'patient_complaint' => $this->patient_complaint,
            'diagnosis' => $this->diagnosis,
            'status' => $this->status,
            'prescriptions' => PrescriptionResource::collection($this->whenLoaded('prescriptions')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

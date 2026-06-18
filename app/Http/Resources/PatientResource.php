<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'email' => $this->user?->email,
            'account_name' => $this->user?->name,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'gender' => $this->gender,
            'address' => $this->address,
            'job' => $this->job,
            'profile_completed' => $this->profile_completed,
            'emergency_contact' => $this->emergency_contact,
            'created_at' => $this->created_at->toDateTimeString(),

            'appointments' => AppointmentResource::collection($this->whenLoaded('appointments')),
        ];
    }
}

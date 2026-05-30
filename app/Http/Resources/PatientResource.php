<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user?->name ?? $this->name,
            'email' => $this->user?->email ?? $this->email,
            'phone' => $this->user?->phone ?? $this->phone,
            'age' => $this->age,
            'gender' => $this->gender,
            'job' => $this->job,
            'address' => $this->address,
            'medical_history' => $this->medical_history,
            'analysis' => $this->analysis,
            'profile_completed' => $this->profile_completed,
            'is_walk_in' => is_null($this->user_id),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
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
            'userId' => $this->user_id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'departmentId' => $this->department_id,
            'department' => $this->department?->name,
            'specialty' => $this->specialty,
            'workingHours' => [
                'start' => $this->working_hours_start,
                'end' => $this->working_hours_end,
            ],
        ];
    }
}

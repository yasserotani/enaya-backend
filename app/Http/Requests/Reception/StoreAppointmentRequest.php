<?php

namespace App\Http\Requests\Reception;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'scheduled_at' => 'required|date_format:Y-m-d H:i:s|after:now',
            'visit_reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}

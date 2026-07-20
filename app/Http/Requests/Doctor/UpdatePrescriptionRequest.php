<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medication_name' => ['sometimes', 'required', 'string', 'max:255'],
            'dosage' => ['sometimes', 'required', 'string', 'max:255'],
            'frequency' => ['sometimes', 'required', 'string', 'max:255'],
            'duration_days' => ['sometimes', 'required', 'integer', 'min:1'],
            'instructions' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

<?php

namespace App\Http\Requests\Patient;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class CompletePatientProfileRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $patientId = $this->user()?->patient?->getKey();

        return [
            'full_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('patients', 'full_name')->ignore($patientId),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('patients', 'phone')->ignore($patientId),
            ],
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'address' => 'nullable|string',
            'job' => 'nullable|string|max:255',
        ];
    }
}

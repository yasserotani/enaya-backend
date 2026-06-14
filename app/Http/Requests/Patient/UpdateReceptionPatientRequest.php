<?php

namespace App\Http\Requests\Patient;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateReceptionPatientRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $patientId = $this->route('patient')?->getKey();

        return [
            'full_name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('patients', 'full_name')->ignore($patientId),
            ],
            'phone' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('patients', 'phone')->ignore($patientId),
            ],
            'date_of_birth' => 'sometimes|nullable|date|before:today',
            'gender' => 'sometimes|in:male,female',
            'address' => 'sometimes|nullable|string',
            'job' => 'sometimes|nullable|string|max:255',
            'emergency_contact' => 'sometimes|nullable|string|max:255',
        ];
    }
}

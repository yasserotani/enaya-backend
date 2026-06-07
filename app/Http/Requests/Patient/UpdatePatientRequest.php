<?php

namespace App\Http\Requests\Patient;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()?->getKey();
        $patientId = $this->user()?->patient?->getKey();

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('users', 'name')->ignore($userId),
            ],
            'phone' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('patients', 'phone')->ignore($patientId),
            ],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'date_of_birth' => 'sometimes|date|before:today',
            'gender' => 'sometimes|in:male,female',
            'address' => 'sometimes|nullable|string',
            'job' => 'sometimes|nullable|string|max:255',
        ];
    }
}

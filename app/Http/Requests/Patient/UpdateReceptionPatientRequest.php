<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReceptionPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'sometimes|string|max:255|unique:patients,full_name,',
            'phone' => 'sometimes|string|max:20|unique:patients,phone,',
            'email' => 'sometimes|nullable|email',
            'date_of_birth' => 'sometimes|nullable|date|before:today',
            'gender' => 'sometimes|in:male,female',
            'address' => 'sometimes|nullable|string',
            'job' => 'sometimes|nullable|string|max:255',
        ];
    }
}

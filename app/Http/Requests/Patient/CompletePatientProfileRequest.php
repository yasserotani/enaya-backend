<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class CompletePatientProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255|unique:patients,full_name,' . auth()->id(),
            'phone' => 'required|numeric|unique:patients,phone,' . auth()->id(),
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'address' => 'nullable|string',
            'job' => 'nullable|string|max:255',
        ];
    }
}

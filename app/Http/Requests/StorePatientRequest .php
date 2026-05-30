<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'job' => 'nullable|string|max:255',
            'medical_history' => 'nullable|string',
            'gender' => 'nullable|in:male,female',
            'age' => 'nullable|integer|min:0|max:120',
        ];
    }
}

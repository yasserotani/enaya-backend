<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceptionPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255|unique:patients,full_name',
            'phone' => 'required|numeric|digits_between:1,20|unique:patients,phone',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'required|in:male,female',
            'address' => 'nullable|string',
            'job' => 'nullable|string|max:255',
        ];
    }
}

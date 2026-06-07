<?php

namespace App\Http\Requests\Patient;

use App\Http\Requests\BaseFormRequest;

class StoreReceptionPatientRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255|unique:patients,full_name',
            'phone' => 'required|string|max:20|unique:patients,phone',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'required|in:male,female',
            'address' => 'nullable|string',
            'job' => 'nullable|string|max:255',
        ];
    }
}

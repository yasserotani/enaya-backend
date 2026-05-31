<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // goes to users table
            'name' => 'sometimes|string|max:255|unique:users,name,' . auth()->id(), // allow current user's name
            'phone' => 'sometimes|string|max:20|unique:patients,phone,' . auth()->id(),
            'email' => 'sometimes|email|unique:users,email,' . auth()->id(), // allow current user's email

            // goes to patients table
            'date_of_birth' => 'sometimes|date|before:today',
            'gender' => 'sometimes|in:male,female',
            'address' => 'sometimes|nullable|string',
            'job' => 'sometimes|nullable|string|max:255',
        ];
    }
}

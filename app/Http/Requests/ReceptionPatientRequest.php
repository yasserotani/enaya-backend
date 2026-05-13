<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceptionPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'age' => 'nullable|integer|min:0|max:120',
            'gender' => 'nullable|in:male,female',
            'job' => 'nullable|string',
            'address' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'analysis' => 'nullable|string',
        ];
    }
}
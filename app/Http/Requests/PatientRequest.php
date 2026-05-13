<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->routeIs('patients.store')) {
            return [
            'job' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'gender' => 'nullable|in:male,female',
            'age' => 'nullable|integer|min:0|max:120',
            ];
        }
        return [
            'job' => 'nullable|string|max:255',

            'analysis' => 'nullable|string',

            'medical_history' => 'nullable|string',

            'gender' => 'required|in:male,female',

            'age' => 'required|integer|min:0|max:120',
        ];
    }
}

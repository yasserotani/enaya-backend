<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'medication_name' => ['required', 'string', 'max:255'],
            'dosage' => ['required', 'string', 'max:255'],
            'frequency' => ['required', 'string', 'max:255'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'instructions' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

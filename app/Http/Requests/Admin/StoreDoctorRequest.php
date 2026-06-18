<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:doctors,phone'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'department_id' => ['required', 'exists:departments,id'],
            'specialty' => ['required', 'string', 'max:255'],
            'working_hours_start' => ['nullable', 'date_format:H:i'],
            'working_hours_end' => ['nullable', 'date_format:H:i', 'after:working_hours_start'],
        ];
    }
}

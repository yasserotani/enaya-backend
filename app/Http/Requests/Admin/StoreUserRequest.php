<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['doctor', 'receptionist', 'admin'])],

            'phone' => ['required_if:role,doctor', 'string', 'max:20', 'unique:doctors,phone'],
            'date_of_birth' => ['required_if:role,doctor', 'date', 'before:today'],
            'gender' => ['required_if:role,doctor', Rule::in(['male', 'female'])],
            'specialty' => ['required_if:role,doctor', 'string', 'max:255'],
            'department_id' => ['required_if:role,doctor', 'integer', 'exists:departments,id'],
            'working_hours_start' => ['nullable', 'date_format:H:i'],
            'working_hours_end' => ['nullable', 'date_format:H:i', 'after:working_hours_start'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}

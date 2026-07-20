<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $doctor = $this->route('doctor');

        return [
            'email' => ['sometimes', 'email', 'unique:users,email,'.$doctor->user_id],
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'unique:doctors,phone,'.$doctor->id],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female'],
            'department_id' => ['sometimes', 'exists:departments,id'],
            'specialty' => ['sometimes', 'string', 'max:255'],
            'working_hours_start' => ['sometimes', 'date_format:H:i'],
            'working_hours_end' => ['sometimes', 'date_format:H:i', 'after:working_hours_start'],
        ];
    }
}

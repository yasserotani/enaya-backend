<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:doctor,receptionist',
            'phone' => 'required_if:role,doctor|string|min:10',
            'date_of_birth' => 'required_if:role,doctor|date',
            'gender' => 'required_if:role,doctor|string|in:male,female',
            'specialty' => 'required_if:role,doctor|string|max:255',
            'department_id' => 'required_if:role,doctor|integer|exists:departments,id',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}

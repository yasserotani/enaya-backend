<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
        return [
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|numeric|unique:users,phone',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ];
    }

    /**
     * Get the body parameters for documentation.
     *
     * @return array<string, mixed>
     */
    public function bodyParameters(): array
    {
        return [
            'username' => [
                'description' => 'The user\'s unique username',
                'example' => 'newuser123',
            ],
            'email' => [
                'description' => 'The user\'s email address',
                'example' => 'newuser@enaya.com',
            ],
            'phone' => [
                'description' => 'The user\'s phone number',
                'example' => '+966501234567',
            ],
            'password' => [
                'description' => 'The user\'s password (minimum 8 characters)',
                'example' => 'password',
            ],
            'password_confirmation' => [
                'description' => 'Password confirmation (must match password)',
                'example' => 'password',
            ],
        ];
    }
}

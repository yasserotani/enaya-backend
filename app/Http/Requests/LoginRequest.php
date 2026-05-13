<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'usernameOrEmail' => 'required',
            'password' => 'required',
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
            'usernameOrEmail' => [
                'description' => 'The user\'s username or email address',
                'example' => 'user@enaya.com',
            ],
            'password' => [
                'description' => 'The user\'s password',
                'example' => 'password',
            ],
        ];
    }
}

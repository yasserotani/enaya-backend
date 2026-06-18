<?php

namespace App\Http\Requests\Reception;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleAppointmentRequest extends FormRequest
{
    public function rules(): array
    {
        return ['scheduled_at' => 'required|date_format:Y-m-d H:i:s|after:now'];

    }

    public function authorize(): bool
    {
        return true;
    }
}

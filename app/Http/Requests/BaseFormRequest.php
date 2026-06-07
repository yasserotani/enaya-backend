<?php

namespace App\Http\Requests;

use App\Services\PhoneNormalizerService;
use Illuminate\Foundation\Http\FormRequest;

class BaseFormRequest extends FormRequest
{
    /**
     * Normalize phone numbers in the request data.
     *
     * Override this method if you need custom normalization logic.
     */
    protected function prepareForValidation(): void
    {
        // Check if the request has a 'phone' field and normalize it
        if ($this->has('phone')) {
            $this->merge([
                'phone' => PhoneNormalizerService::normalize($this->input('phone')),
            ]);
        }
    }
}

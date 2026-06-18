<?php

namespace App\Http\Requests\Doctor;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\Validator;

class UpdateDoctorWorkingHoursRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'working_hours_start' => ['sometimes', 'date_format:H:i'],
            'working_hours_end' => ['sometimes', 'date_format:H:i'],
        ];
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        if ($this->has('working_hours_start')) {
            $this->merge([
                'working_hours_start' => str_pad($this->input('working_hours_start'), 5, '0', STR_PAD_LEFT),
            ]);
        }

        if ($this->has('working_hours_end')) {
            $this->merge([
                'working_hours_end' => str_pad($this->input('working_hours_end'), 5, '0', STR_PAD_LEFT),
            ]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('working_hours_start') && ! $this->filled('working_hours_end')) {
                $validator->errors()->add('working_hours', 'At least one working hour field is required.');
            }

            if ($this->filled('working_hours_start') && $this->filled('working_hours_end')) {
                $start = $this->input('working_hours_start');
                $end = $this->input('working_hours_end');

                if ($end <= $start) {
                    $validator->errors()->add('working_hours_end', 'The end time must be after the start time.');
                }
            }
        });
    }
}

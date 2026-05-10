<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'date'       => ['required', 'date'],
            'venue'      => ['nullable', 'string', 'max:255'],
            'time_type'  => ['required', 'in:HALF_DAY,FULL_DAY'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time'   => ['nullable', 'date_format:H:i', 'after:start_time'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Event name is required.',
            'date.required'      => 'Event date is required.',
            'time_type.required' => 'Please select Half Day or Full Day.',
            'time_type.in'       => 'Time type must be Half Day or Full Day.',
            'end_time.after'     => 'End time must be after start time.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'cleaning_service' => ['required', 'string', 'max:255'],
            'booking_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'finish_time' => ['nullable', 'date_format:H:i'],
            'technician' => ['nullable', 'string', 'max:255'],
            'priority' => ['required', 'in:Low,Normal,High,Urgent'],
            'status' => ['required', 'in:Pending,In Progress,Completed,Cancelled'],
            'internal_notes' => ['nullable', 'string'],
        ];
    }
}

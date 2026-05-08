<?php

namespace App\Http\Requests;

use App\Enums\LeaveType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Tout agent authentifié peut demander
    }

    public function rules(): array
    {
        return [
            'agent_id'   => ['required', 'exists:agents,id'],
            'type'       => ['required', Rule::enum(LeaveType::class)],
            'date_start' => ['required', 'date', 'after_or_equal:today'],
            'date_end'   => ['required', 'date', 'after_or_equal:date_start'],
            'reason'     => ['nullable', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}

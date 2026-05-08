<?php

namespace App\Http\Requests;

use App\Enums\SlotType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanningSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['admin', 'rh', 'manager']);
    }

    public function rules(): array
    {
        return [
            'agent_id'   => ['required', 'exists:agents,id'],
            'date'       => ['required', 'date'],
            'time_start' => ['required', 'date_format:H:i'],
            'time_end'   => ['required', 'date_format:H:i', 'after:time_start'],
            'type'       => ['required', Rule::enum(SlotType::class)],
            'client_id'  => ['nullable', 'exists:clients,id'],
            'mission_id' => ['nullable', 'exists:missions,id'],
            'note'       => ['nullable', 'string', 'max:500'],
        ];
    }
}

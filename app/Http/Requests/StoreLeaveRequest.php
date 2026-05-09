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
            'agent_id'     => ['required', 'exists:agents,id'],
            'type'         => ['required', Rule::enum(LeaveType::class)],
            'date_start'   => ['required', 'date'],
            'date_end'     => ['required', 'date', 'after_or_equal:date_start'],
            'working_days' => [
                'required',
                'numeric',
                'min:0.5',
                'max:60',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (fmod((float) $value * 2, 1) !== 0.0) {
                        $fail('Le nombre de jours doit être un multiple de 0.5.');
                    }
                },

            ],
            'reason'       => ['nullable', 'string', 'max:1000'],
            'attachment'   => ['nullable', 'file', 'mimes:pdf,jpg,png', 'max:2048'],
        ];
    }
}

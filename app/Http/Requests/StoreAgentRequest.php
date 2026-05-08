<?php

namespace App\Http\Requests;

use App\Enums\ContractType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['admin', 'rh']);
    }

    public function rules(): array
    {
        $agentId = $this->route('agent')?->id;

        return [
            'first_name'        => ['required', 'string', 'max:100'],
            'last_name'         => ['required', 'string', 'max:100'],
            'email'             => ['required', 'email', Rule::unique('users', 'email')->ignore($agentId ? $this->route('agent')->user_id : null)],
            'password'          => [$agentId ? 'nullable' : 'required', 'string', 'min:8'],
            'department_id'     => ['required', 'exists:departments,id'],
            'manager_id'        => ['nullable', 'exists:agents,id'],
            'employee_code'     => ['required', 'string', 'max:20', Rule::unique('agents')->ignore($agentId)],
            'phone'             => ['nullable', 'string', 'max:20'],
            'contract_type'     => ['required', Rule::enum(ContractType::class)],
            'hire_date'         => ['required', 'date'],
            'contract_end_date' => ['nullable', 'date', 'after:hire_date'],
            'weekly_hours'      => ['required', 'integer', 'min:1', 'max:60'],
            'annual_leave_days' => ['required', 'integer', 'min:0', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_code.unique' => 'Ce code employé est déjà utilisé.',
            'email.unique'         => 'Cet email est déjà utilisé.',
        ];
    }
}

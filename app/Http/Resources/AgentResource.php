<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'employee_code'     => $this->employee_code,
            'first_name'        => $this->first_name,
            'last_name'         => $this->last_name,
            'full_name'         => $this->full_name,
            'phone'             => $this->phone,
            'avatar_url'        => $this->avatar_url,
            'contract_type'     => [
                'value' => $this->contract_type->value,
                'label' => $this->contract_type->label(),
            ],
            'hire_date'         => $this->hire_date?->format('Y-m-d'),
            'contract_end_date' => $this->contract_end_date?->format('Y-m-d'),
            'weekly_hours'      => $this->weekly_hours,
            'annual_leave_days' => $this->annual_leave_days,
            'is_active'         => $this->is_active,
            'department'        => new DepartmentResource($this->whenLoaded('department')),
            'manager'           => new AgentResource($this->whenLoaded('manager')),
            'user'              => [
                'id'    => $this->whenLoaded('user', fn() => $this->user->id),
                'email' => $this->whenLoaded('user', fn() => $this->user->email),
                'roles' => $this->whenLoaded('user', fn() => $this->user->getRoleNames()),
            ],
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

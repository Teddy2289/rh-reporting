<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'agent_id'       => $this->agent_id,
            'type'           => [
                'value' => $this->type->value,
                'label' => $this->type->label(),
            ],
            'status'         => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'date_start'     => $this->date_start->format('Y-m-d'),
            'date_end'       => $this->date_end->format('Y-m-d'),
            'working_days'   => $this->working_days,
            'reason'         => $this->reason,
            'refusal_reason' => $this->refusal_reason,
            'approved_at'    => $this->approved_at?->toISOString(),
            'attachment_url' => $this->attachment ? asset("storage/{$this->attachment}") : null,
            'agent'          => new AgentResource($this->whenLoaded('agent')),
            'approved_by'    => $this->whenLoaded('approvedBy', fn() => [
                'id'   => $this->approvedBy->id,
                'name' => $this->approvedBy->name,
            ]),
            'created_at'     => $this->created_at?->toISOString(),
        ];
    }
}

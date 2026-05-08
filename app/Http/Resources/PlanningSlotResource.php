<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanningSlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'agent_id'         => $this->agent_id,
            'client_id'        => $this->client_id,
            'mission_id'       => $this->mission_id,
            'date'             => $this->date->format('Y-m-d'),
            'time_start'       => $this->time_start,
            'time_end'         => $this->time_end,
            'duration_minutes' => $this->duration_minutes,
            'duration_hours'   => $this->duration_hours,
            'type'             => [
                'value' => $this->type->value,
                'label' => $this->type->label(),
                'is_worked' => $this->type->isWorked(),
            ],
            'note'             => $this->note,
            'is_confirmed'     => $this->is_confirmed,
            'agent'            => new AgentResource($this->whenLoaded('agent')),
            'client'           => new ClientResource($this->whenLoaded('client')),
            'mission'          => new MissionResource($this->whenLoaded('mission')),
            'created_at'       => $this->created_at?->toISOString(),
        ];
    }
}

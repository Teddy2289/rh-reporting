<?php

namespace App\Observers;

use App\Models\HourLog;
use App\Models\PlanningSlot;
use App\Enums\SlotType;

class PlanningSlotObserver
{
    /**
     * Recalcule l'HourLog du jour concerné à chaque modification d'un slot.
     */
    public function saved(PlanningSlot $slot): void
    {
        $this->recalculateHourLog($slot);
    }

    public function deleted(PlanningSlot $slot): void
    {
        $this->recalculateHourLog($slot);
    }

    private function recalculateHourLog(PlanningSlot $slot): void
    {
        $agentId = $slot->agent_id;
        $date    = $slot->date->format('Y-m-d');

        // Somme de tous les slots "work" de l'agent pour ce jour
        $workedMinutes = PlanningSlot::where('agent_id', $agentId)
            ->where('date', $date)
            ->where('type', SlotType::Work->value)
            ->get()
            ->sum('duration_minutes');

        $expectedMinutes = $slot->agent->daily_expected_minutes;
        $overtimeMinutes = max(0, $workedMinutes - $expectedMinutes);

        HourLog::updateOrCreate(
            ['agent_id' => $agentId, 'date' => $date],
            [
                'worked_minutes'   => $workedMinutes,
                'expected_minutes' => $expectedMinutes,
                'overtime_minutes' => $overtimeMinutes,
            ]
        );
    }
}

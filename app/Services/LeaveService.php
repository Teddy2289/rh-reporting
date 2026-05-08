<?php

namespace App\Services;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\Agent;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\PlanningSlot;
use App\Enums\SlotType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    /**
     * Calcule le nombre de jours ouvrés entre deux dates (hors week-ends).
     */
    public function calculateWorkingDays(Carbon $start, Carbon $end): int
    {
        $period = CarbonPeriod::create($start, $end);
        return collect($period)
            ->filter(fn($date) => !$date->isWeekend())
            ->count();
    }

    /**
     * Vérifie que l'agent a suffisamment de solde.
     */
    public function hasEnoughBalance(Agent $agent, LeaveType $type, int $workingDays): bool
    {
        if (!$type->deductsBalance()) {
            return true;
        }

        $balance = $this->getOrCreateBalance($agent, now()->year);
        return $balance->remaining_days >= $workingDays;
    }

    /**
     * Crée ou retourne le solde congé d'un agent pour une année.
     */
    public function getOrCreateBalance(Agent $agent, int $year): LeaveBalance
    {
        return LeaveBalance::firstOrCreate(
            ['agent_id' => $agent->id, 'year' => $year],
            [
                'allocated_days'    => $agent->annual_leave_days,
                'used_days'         => 0,
                'pending_days'      => 0,
                'carried_over_days' => 0,
            ]
        );
    }

    /**
     * Soumet une demande de congé.
     */
    public function requestLeave(Agent $agent, array $data): Leave
    {
        $start       = Carbon::parse($data['date_start']);
        $end         = Carbon::parse($data['date_end']);
        $type        = LeaveType::from($data['type']);
        $workingDays = $this->calculateWorkingDays($start, $end);

        if ($type->deductsBalance() && !$this->hasEnoughBalance($agent, $type, $workingDays)) {
            throw new \Exception("Solde de congés insuffisant. Solde disponible : {$this->getOrCreateBalance($agent, $start->year)->remaining_days} jours.");
        }

        return DB::transaction(function () use ($agent, $data, $workingDays, $type, $start) {
            $leave = Leave::create([
                'agent_id'    => $agent->id,
                'type'        => $type->value,
                'status'      => LeaveStatus::Pending->value,
                'date_start'  => $data['date_start'],
                'date_end'    => $data['date_end'],
                'working_days' => $workingDays,
                'reason'      => $data['reason'] ?? null,
                'attachment'  => $data['attachment'] ?? null,
            ]);

            // Réserve les jours en "pending"
            if ($type->deductsBalance()) {
                $balance = $this->getOrCreateBalance($agent, $start->year);
                $balance->increment('pending_days', $workingDays);
            }

            return $leave;
        });
    }

    /**
     * Approuve un congé et génère les slots leave dans le planning.
     */
    public function approveLeave(Leave $leave, int $approvedBy): Leave
    {
        return DB::transaction(function () use ($leave, $approvedBy) {
            $leave->update([
                'status'      => LeaveStatus::Approved->value,
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            // Met à jour le solde
            if ($leave->type->deductsBalance()) {
                $balance = $this->getOrCreateBalance($leave->agent, $leave->date_start->year);
                $balance->decrement('pending_days', $leave->working_days);
                $balance->increment('used_days', $leave->working_days);
            }

            // Génère les slots de type "leave" dans le planning
            $this->generateLeaveSlots($leave);

            return $leave->fresh();
        });
    }

    /**
     * Refuse un congé.
     */
    public function refuseLeave(Leave $leave, int $approvedBy, string $reason): Leave
    {
        return DB::transaction(function () use ($leave, $approvedBy, $reason) {
            $leave->update([
                'status'         => LeaveStatus::Refused->value,
                'approved_by'    => $approvedBy,
                'approved_at'    => now(),
                'refusal_reason' => $reason,
            ]);

            // Libère les jours "pending"
            if ($leave->type->deductsBalance()) {
                $balance = $this->getOrCreateBalance($leave->agent, $leave->date_start->year);
                $balance->decrement('pending_days', $leave->working_days);
            }

            return $leave->fresh();
        });
    }

    /**
     * Génère les PlanningSlots de type "leave" pour chaque jour ouvré du congé.
     */
    private function generateLeaveSlots(Leave $leave): void
    {
        $period = CarbonPeriod::create($leave->date_start, $leave->date_end);
        $agent  = $leave->agent;

        foreach ($period as $date) {
            if ($date->isWeekend()) continue;

            // Calcule les heures attendues ce jour-là
            $start = '09:00';
            $end   = '17:30'; // 8h30 moins 30min de pause = 8h

            // Supprime les anciens slots de travail pour ce jour (s'il y en a)
            PlanningSlot::where('agent_id', $agent->id)
                ->where('date', $date->format('Y-m-d'))
                ->delete();

            PlanningSlot::create([
                'agent_id'   => $agent->id,
                'date'       => $date->format('Y-m-d'),
                'time_start' => $start,
                'time_end'   => $end,
                'type'       => SlotType::Leave->value,
                'note'       => $leave->type->label(),
                'created_by' => $leave->approved_by,
            ]);
        }
    }
}

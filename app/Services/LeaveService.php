<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Enums\LeaveStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveService
{
    const MONTHLY_ACCRUAL = 2.5; // 2.5 jours par mois
    const MAX_CARRIED_OVER = 15; // Maximum de jours reportables

    /**
     * Demander un congé
     */
    public function requestLeave(Agent $agent, array $data): Leave
    {
        return DB::transaction(function () use ($agent, $data) {
            // Calculer les jours ouvrés
            $workingDays = $this->calculateWorkingDays(
                Carbon::parse($data['date_start']),
                Carbon::parse($data['date_end'])
            );

            $data['working_days'] = $workingDays;
            $data['status'] = LeaveStatus::Pending;
            $data['agent_id'] = $agent->id;

            // Vérifier le solde
            $balance = $this->getOrCreateBalance($agent, Carbon::parse($data['date_start'])->year);

            if (!$balance->canTakeLeave($workingDays)) {
                throw new \Exception("Solde de congés insuffisant. Solde restant : {$balance->remaining_days} jours");
            }

            // Réserver les jours (pending)
            $balance->deductPendingDays($workingDays);

            return Leave::create($data);
        });
    }

    /**
     * Approuver un congé
     */
    public function approveLeave(Leave $leave, int $approvedBy): Leave
    {
        return DB::transaction(function () use ($leave, $approvedBy) {
            $balance = $this->getOrCreateBalance($leave->agent, $leave->date_start->year);

            // Transformer pending en used
            $balance->approvePendingDays($leave->working_days);

            $leave->update([
                'status' => LeaveStatus::Approved,
                'approved_by' => $approvedBy,
                'approved_at' => now()
            ]);

            return $leave->fresh();
        });
    }

    /**
     * Refuser un congé
     */
    public function refuseLeave(Leave $leave, int $approvedBy, string $reason): Leave
    {
        return DB::transaction(function () use ($leave, $approvedBy, $reason) {
            $balance = $this->getOrCreateBalance($leave->agent, $leave->date_start->year);

            // Libérer les jours réservés
            $balance->rejectPendingDays($leave->working_days);

            $leave->update([
                'status' => LeaveStatus::Refused,
                'approved_by' => $approvedBy,
                'refusal_reason' => $reason
            ]);

            return $leave->fresh();
        });
    }

    /**
     * Calculer les jours ouvrés (lundi-vendredi)
     */
    public function calculateWorkingDays(Carbon $start, Carbon $end): float
    {
        $days = 0;
        $current = $start->copy();

        while ($current <= $end) {
            // Lundi = 1, Dimanche = 7
            if ($current->dayOfWeek !== Carbon::SATURDAY && $current->dayOfWeek !== Carbon::SUNDAY) {
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }

    /**
     * Obtenir ou créer le solde de congés
     */
    public function getOrCreateBalance(Agent $agent, int $year): LeaveBalance
    {
        $balance = LeaveBalance::firstOrCreate(
            ['agent_id' => $agent->id, 'year' => $year],
            [
                'allocated_days' => 0,
                'used_days' => 0,
                'pending_days' => 0,
                'carried_over_days' => 0,
                'last_updated_at' => now()
            ]
        );

        // Si c'est la première fois qu'on crée le solde pour cette année
        if ($balance->wasRecentlyCreated) {
            $this->initializeYearlyBalance($agent, $year, $balance);
        }

        return $balance;
    }

    /**
     * Initialiser le solde pour une nouvelle année
     */
    protected function initializeYearlyBalance(Agent $agent, int $year, LeaveBalance $balance): void
    {
        // Calculer les jours acquis (2.5 par mois travaillé)
        $monthsWorked = $this->calculateMonthsWorked($agent->hire_date, $year);
        $allocatedDays = $monthsWorked * self::MONTHLY_ACCRUAL;

        // Reporter les jours non utilisés de l'année précédente (limité)
        if ($year > date('Y')) {
            $previousBalance = LeaveBalance::query()
                ->where('agent_id', $agent->id)
                ->where('year', $year - 1)
                ->first();

            if ($previousBalance) {
                $carryOver = min($previousBalance->remaining_days, self::MAX_CARRIED_OVER);
                $balance->carried_over_days = $carryOver;
            }
        }

        $balance->allocated_days = $allocatedDays;
        $balance->save();
    }

    /**
     * Calculer les mois travaillés dans l'année
     */
    protected function calculateMonthsWorked(Carbon $hireDate, int $year): int
    {
        if ($hireDate->year > $year) return 0;

        $startMonth = ($hireDate->year == $year) ? $hireDate->month : 1;
        $currentMonth = Carbon::now()->year == $year ? Carbon::now()->month : 12;

        return max(0, $currentMonth - $startMonth + 1);
    }

    /**
     * CRON JOB - Ajouter 2.5 jours au début de chaque mois
     * À exécuter le 1er de chaque mois
     */
    public function addMonthlyLeaveDays(): void
    {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        // Récupérer tous les agents actifs
        $agents = Agent::query()->where('is_active', true)->get();

        foreach ($agents as $agent) {
            DB::transaction(function () use ($agent, $currentYear, $currentMonth) {
                $balance = $this->getOrCreateBalance($agent, $currentYear);

                // Vérifier si l'agent a travaillé ce mois-ci
                if ($this->shouldAccrueLeave($agent, $currentYear, $currentMonth)) {
                    $balance->addAllocatedDays(self::MONTHLY_ACCRUAL);

                    Log::info("Ajout de " . self::MONTHLY_ACCRUAL . " jours de congé pour l'agent {$agent->id} - Mois {$currentMonth}/{$currentYear}");
                }
            });
        }
    }

    /**
     * Vérifier si l'agent a droit aux congés pour ce mois
     */
    protected function shouldAccrueLeave(Agent $agent, int $year, int $month): bool
    {
        // L'agent doit être embauché avant la fin du mois
        if ($agent->hire_date->year > $year) return false;
        if ($agent->hire_date->year == $year && $agent->hire_date->month > $month) return false;

        // Vérifier si le contrat n'est pas terminé
        if ($agent->contract_end_date && $agent->contract_end_date->year < $year) return false;
        if ($agent->contract_end_date && $agent->contract_end_date->year == $year && $agent->contract_end_date->month < $month) return false;

        return true;
    }

    /**
     * CRON JOB - Recalculer les soldes en début d'année
     * À exécuter le 1er janvier
     */
    public function processNewYearRollover(): void
    {
        $lastYear = Carbon::now()->subYear()->year;
        $currentYear = Carbon::now()->year;

        // Récupérer tous les soldes de l'année dernière
        $oldBalances = LeaveBalance::query()->where('year', $lastYear)->get();

        foreach ($oldBalances as $oldBalance) {
            $remaining = $oldBalance->remaining_days;

            if ($remaining > 0) {
                // Créer ou mettre à jour le solde de la nouvelle année
                $newBalance = LeaveBalance::firstOrCreate(
                    ['agent_id' => $oldBalance->agent_id, 'year' => $currentYear],
                    [
                        'allocated_days' => 0,
                        'used_days' => 0,
                        'pending_days' => 0,
                        'carried_over_days' => 0
                    ]
                );

                // Reporter les jours (avec limite)
                $carryOver = min($remaining, self::MAX_CARRIED_OVER);
                $newBalance->carried_over_days = $carryOver;
                $newBalance->save();

                Log::info("Report de {$carryOver} jours pour l'agent {$oldBalance->agent_id} vers {$currentYear}");
            }
        }
    }
}

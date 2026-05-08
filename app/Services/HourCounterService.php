<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\HourLog;
use App\Models\PlanningSlot;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class HourCounterService
{
    /**
     * Retourne le détail des heures travaillées pour un agent sur une période.
     */
    public function getWorkedHours(Agent $agent, Carbon $from, Carbon $to): array
    {
        $logs = HourLog::forAgent($agent->id)
            ->betweenDates($from->format('Y-m-d'), $to->format('Y-m-d'))
            ->get();

        $totalWorked   = $logs->sum('worked_minutes');
        $totalExpected = $logs->sum('expected_minutes');
        $totalOvertime = $logs->sum('overtime_minutes');

        // Détail par client sur la même période
        $byClient = PlanningSlot::forAgent($agent->id)
            ->betweenDates($from->format('Y-m-d'), $to->format('Y-m-d'))
            ->worked()
            ->with('client')
            ->get()
            ->groupBy('client_id')
            ->map(fn($slots) => [
                'client'          => optional($slots->first()->client)->name ?? 'Sans client',
                'worked_minutes'  => $slots->sum('duration_minutes'),
                'worked_hours'    => round($slots->sum('duration_minutes') / 60, 2),
                'slots_count'     => $slots->count(),
            ])
            ->values();

        // Détail par mission
        $byMission = PlanningSlot::forAgent($agent->id)
            ->betweenDates($from->format('Y-m-d'), $to->format('Y-m-d'))
            ->worked()
            ->whereNotNull('mission_id')
            ->with('mission.client')
            ->get()
            ->groupBy('mission_id')
            ->map(fn($slots) => [
                'mission'        => optional($slots->first()->mission)->name ?? 'Sans mission',
                'client'         => optional(optional($slots->first()->mission)->client)->name ?? '-',
                'worked_minutes' => $slots->sum('duration_minutes'),
                'worked_hours'   => round($slots->sum('duration_minutes') / 60, 2),
            ])
            ->values();

        return [
            'agent'            => $agent->full_name,
            'period'           => [
                'from' => $from->format('Y-m-d'),
                'to'   => $to->format('Y-m-d'),
            ],
            'total_worked_minutes'   => $totalWorked,
            'total_worked_hours'     => round($totalWorked / 60, 2),
            'total_expected_minutes' => $totalExpected,
            'total_expected_hours'   => round($totalExpected / 60, 2),
            'total_overtime_minutes' => $totalOvertime,
            'total_overtime_hours'   => round($totalOvertime / 60, 2),
            'days_worked'            => $logs->where('worked_minutes', '>', 0)->count(),
            'by_client'              => $byClient,
            'by_mission'             => $byMission,
        ];
    }

    /**
     * Retourne le résumé mensuel d'un agent.
     */
    public function getMonthlySummary(Agent $agent, int $year, int $month): array
    {
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to   = Carbon::create($year, $month, 1)->endOfMonth();

        return $this->getWorkedHours($agent, $from, $to);
    }

    /**
     * Retourne le résumé annuel d'un agent.
     */
    public function getAnnualSummary(Agent $agent, int $year): array
    {
        $from = Carbon::create($year, 1, 1)->startOfYear();
        $to   = Carbon::create($year, 12, 31)->endOfYear();

        return $this->getWorkedHours($agent, $from, $to);
    }

    /**
     * Statistiques globales pour le dashboard RH.
     */
    public function getDashboardStats(int $year, int $month): array
    {
        $logs = HourLog::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('agent.department')
            ->get();

        $byDepartment = $logs->groupBy('agent.department_id')
            ->map(fn($deptLogs) => [
                'department'         => optional(optional($deptLogs->first()->agent)->department)->name ?? '-',
                'agents_count'       => $deptLogs->pluck('agent_id')->unique()->count(),
                'total_worked_hours' => round($deptLogs->sum('worked_minutes') / 60, 2),
                'total_overtime_hours' => round($deptLogs->sum('overtime_minutes') / 60, 2),
            ])
            ->values();

        return [
            'month'              => $month,
            'year'               => $year,
            'total_agents'       => $logs->pluck('agent_id')->unique()->count(),
            'total_worked_hours' => round($logs->sum('worked_minutes') / 60, 2),
            'total_overtime_hours' => round($logs->sum('overtime_minutes') / 60, 2),
            'by_department'      => $byDepartment,
        ];
    }
}

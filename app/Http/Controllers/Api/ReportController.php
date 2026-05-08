<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Services\HourCounterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private HourCounterService $hourCounter) {}

    /**
     * GET /api/reports/hours?agent_id=1&year=2026&month=4
     */
    public function agentHours(Request $request): JsonResponse
    {
        $request->validate([
            'agent_id'   => ['required', 'exists:agents,id'],
            'year'       => ['required', 'integer'],
            'month'      => ['nullable', 'integer', 'min:1', 'max:12'],
            'date_from'  => ['nullable', 'date'],
            'date_to'    => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $agent = Agent::findOrFail($request->integer('agent_id'));

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $from   = \Carbon\Carbon::parse($request->string('date_from'));
            $to     = \Carbon\Carbon::parse($request->string('date_to'));
            $report = $this->hourCounter->getWorkedHours($agent, $from, $to);
        } elseif ($request->filled('month')) {
            $report = $this->hourCounter->getMonthlySummary($agent, $request->integer('year'), $request->integer('month'));
        } else {
            $report = $this->hourCounter->getAnnualSummary($agent, $request->integer('year'));
        }

        return response()->json($report);
    }

    /**
     * GET /api/reports/dashboard?year=2026&month=4
     */
    public function dashboard(Request $request): JsonResponse
    {
        $request->validate([
            'year'  => ['required', 'integer'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $stats = $this->hourCounter->getDashboardStats(
            $request->integer('year'),
            $request->integer('month')
        );

        return response()->json($stats);
    }

    /**
     * GET /api/reports/team?manager_id=1&year=2026&month=4
     */
    public function teamReport(Request $request): JsonResponse
    {
        $request->validate([
            'manager_id' => ['required', 'exists:agents,id'],
            'year'       => ['required', 'integer'],
            'month'      => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $manager = Agent::with('subordinates')->findOrFail($request->integer('manager_id'));
        $agents  = $manager->subordinates->push($manager);

        $reports = $agents->map(function (Agent $agent) use ($request) {
            return $this->hourCounter->getMonthlySummary(
                $agent,
                $request->integer('year'),
                $request->integer('month')
            );
        });

        return response()->json(['team_reports' => $reports]);
    }

    /**
     * GET /api/reports/export?agent_id=1&year=2026&month=4&format=json
     * Pour l'export Excel, utiliser Maatwebsite Laravel Excel
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'year'  => ['required', 'integer'],
            'month' => ['nullable', 'integer'],
        ]);

        // Récupère les données brutes pour export
        $agents = Agent::active()->with(['department'])->get();
        $data   = [];

        foreach ($agents as $agent) {
            if ($request->filled('month')) {
                $summary = $this->hourCounter->getMonthlySummary($agent, $request->integer('year'), $request->integer('month'));
            } else {
                $summary = $this->hourCounter->getAnnualSummary($agent, $request->integer('year'));
            }
            $data[] = $summary;
        }

        return response()->json(['export_data' => $data]);
    }
}

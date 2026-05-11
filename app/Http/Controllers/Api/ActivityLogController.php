<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Agent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::with(['agent', 'client'])
            ->orderBy('date');

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->integer('agent_id'));
        }
        if ($request->filled('year') && $request->filled('month')) {
            $query->whereYear('date', $request->integer('year'))
                ->whereMonth('date', $request->integer('month'));
        }

        return response()->json($query->get());
    }

    // POST /api/activity-logs/bulk
    // Sauvegarde toute la feuille d'un agent pour un mois
    public function bulkStore(Request $request): JsonResponse
    {
      $request->validate([
        'agent_id'              => ['required', 'exists:agents,id'],
        'month'                 => ['required', 'integer', 'min:1', 'max:12'],
        'year'                  => ['required', 'integer'],
        'entries'               => ['required', 'array'],
        'entries.*.date'        => ['required', 'date'],
        'entries.*.client_id'   => ['required', 'exists:clients,id'],
        'entries.*.mission'     => ['nullable', 'string'], // Changé ici : mission (string) au lieu de mission_id
        'entries.*.description' => ['nullable', 'string'],
    ]);

        // Remplace toutes les entrées du mois pour cet agent
        ActivityLog::query()->where('agent_id', $request->integer('agent_id'))
            ->whereYear('date', $request->integer('year'))
            ->whereMonth('date', $request->integer('month'))
            ->delete();

        $rows = collect($request->input('entries'))->map(fn($e) => [
        'agent_id'    => $request->integer('agent_id'),
        'client_id'   => $e['client_id'],
        'mission'     => $e['mission'] ?? null, // Changé ici : mission au lieu de mission_id
        'date'        => $e['date'],
        'description' => $e['description'] ?? null,
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);
        ActivityLog::insert($rows->toArray());

        return response()->json([
            'message' => 'Activités enregistrées.',
            'count'   => $rows->count(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanningSlotRequest;
use App\Http\Resources\PlanningSlotResource;
use App\Models\Agent;
use App\Models\PlanningSlot;
use App\Services\PlanningGeneratorService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlanningSlotController extends Controller
{
    use AuthorizesRequests;
    public function __construct(private PlanningGeneratorService $generator) {}

    /**
     * GET /api/planning?agent_id=1&date_from=2026-04-01&date_to=2026-04-30
     * GET /api/planning?agent_id=1&year=2026&month=4
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PlanningSlot::with(['agent.department', 'client', 'mission'])
            ->orderBy('date')
            ->orderBy('time_start');

        // Filtres obligatoires selon rôle
        $user = $request->user();
        if ($user->hasRole('agent') && !$user->hasAnyRole(['admin', 'rh', 'manager'])) {
            $query->forAgent($user->agent->id);
        } elseif ($request->filled('agent_id')) {
            $query->forAgent($request->integer('agent_id'));
        }

        // Filtre département
        if ($request->filled('department_id')) {
            $query->whereHas('agent', fn($q) => $q->where('department_id', $request->integer('department_id')));
        }

        // Filtre période
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->betweenDates($request->string('date_from'), $request->string('date_to'));
        } elseif ($request->filled('year') && $request->filled('month')) {
            $query->forMonth($request->integer('year'), $request->integer('month'));
        } elseif ($request->filled('date')) {
            $query->forDate($request->string('date'));
        }

        // Filtre client
        if ($request->filled('client_id')) {
            $query->forClient($request->integer('client_id'));
        }

        return PlanningSlotResource::collection($query->get());
    }

    public function store(StorePlanningSlotRequest $request): JsonResponse
    {
        $this->authorize('create', PlanningSlot::class);
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $slot = PlanningSlot::create($data);

        return response()->json(
            new PlanningSlotResource($slot->load(['agent', 'client', 'mission'])),
            201
        );
    }

    public function show(PlanningSlot $planningSlot): PlanningSlotResource
    {
        $this->authorize('view', $planningSlot);
        return new PlanningSlotResource($planningSlot->load(['agent', 'client', 'mission']));
    }

    public function update(StorePlanningSlotRequest $request, PlanningSlot $planningSlot): PlanningSlotResource
    {
        $this->authorize('update', $planningSlot);
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;

        $planningSlot->update($data);

        return new PlanningSlotResource($planningSlot->fresh(['agent', 'client', 'mission']));
    }

    public function destroy(PlanningSlot $planningSlot): JsonResponse
    {
        $this->authorize('delete', $planningSlot);
        $planningSlot->forceDelete();
        return response()->json(['message' => 'Créneau supprimé.']);
    }

    /**
     * POST /api/planning/generate
     * Génère le planning annuel pour tous les agents ou un seul.
     */
public function generate(Request $request): JsonResponse
    {
        $this->authorize('create', PlanningSlot::class);

        $request->validate([
            'year'      => ['required', 'integer', 'min:2020', 'max:2050'],
            'agent_id'  => ['nullable', 'exists:agents,id'],
            'overwrite' => ['boolean'],
        ]);

        $year      = $request->integer('year');
        $overwrite = $request->boolean('overwrite', false);

        if ($request->filled('agent_id')) {
            $agent   = Agent::findOrFail($request->integer('agent_id'));
            $count   = $this->generator->generateForAgent($agent, $year, $overwrite);
            $results = [['agent' => $agent->full_name, 'created' => $count]];
        } else {
            $results = $this->generator->generateForAll($year, $overwrite);
        }

        return response()->json([
            'message' => 'Planning généré avec succès.',
            'results' => $results,
        ]);
    }

    /**
     * POST /api/planning/bulk
     * Mise à jour en masse (assigner client/mission à plusieurs slots).
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $this->authorize('create', PlanningSlot::class);

        $request->validate([
            'slot_ids'   => ['required', 'array', 'min:1'],
            'slot_ids.*' => ['integer', 'exists:planning_slots,id'],
            'client_id'  => ['nullable', 'exists:clients,id'],
            'mission_id' => ['nullable', 'exists:missions,id'],
        ]);

        $ids = $request->array('slot_ids');

        // Utilisation d'une variable typée pour calmer Intelephense
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = PlanningSlot::whereIn('id', $ids);

        $query->update([
            'client_id'  => $request->input('client_id'),
            'mission_id' => $request->input('mission_id'),
            'updated_by' => $request->user()->id,
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Créneaux mis à jour.',
            'count' => count($ids)
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Resources\LeaveResource;
use App\Models\Agent;
use App\Models\Leave;
use App\Services\LeaveService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeaveController extends Controller
{
    use AuthorizesRequests;
    public function __construct(private LeaveService $leaveService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Leave::with(['agent.department', 'approvedBy'])
            ->orderByDesc('created_at');

        $user = $request->user();

        // Filtre par rôle
        if ($user->hasRole('agent') && !$user->hasAnyRole(['admin', 'rh', 'manager'])) {
            $query->where('agent_id', $user->agent->id);
        } elseif ($user->hasRole('manager') && !$user->hasAnyRole(['admin', 'rh'])) {
            $managerId = $user->agent?->id;
            $query->whereHas('agent', fn($q) => $q->where('manager_id', $managerId));
        }

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->integer('agent_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('year')) {
            $query->forYear($request->integer('year'));
        }
        if ($request->filled('department_id')) {
            $query->whereHas('agent', fn($q) => $q->where('department_id', $request->integer('department_id')));
        }

        return LeaveResource::collection($query->paginate($request->integer('per_page', 20)));
    }

    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $agent = Agent::findOrFail($request->integer('agent_id'));

        // Vérifier que l'agent connecté ne demande que pour lui-même (sauf RH/Admin)
        if ($request->user()->hasRole('agent') && !$request->user()->hasAnyRole(['admin', 'rh'])) {
            if ($request->user()->agent?->id !== $agent->id) {
                abort(403, 'Vous ne pouvez demander un congé que pour vous-même.');
            }
        }

        try {
            $leave = $this->leaveService->requestLeave($agent, $request->validated());
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(new LeaveResource($leave->load(['agent', 'approvedBy'])), 201);
    }

    public function show(Leave $leave): LeaveResource
    {
        $this->authorize('view', $leave);
        return new LeaveResource($leave->load(['agent.department', 'approvedBy']));
    }

    public function approve(Request $request, Leave $leave): JsonResponse
    {
        $this->authorize('approve', $leave);

        if (!$leave->isPending()) {
            return response()->json(['message' => 'Ce congé n\'est plus en attente.'], 422);
        }

        $leave = $this->leaveService->approveLeave($leave, $request->user()->id);

        return response()->json([
            'message' => 'Congé approuvé et planning mis à jour.',
            'leave'   => new LeaveResource($leave),
        ]);
    }

    public function refuse(Request $request, Leave $leave): JsonResponse
    {
        $this->authorize('approve', $leave);

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if (!$leave->isPending()) {
            return response()->json(['message' => 'Ce congé n\'est plus en attente.'], 422);
        }

        $leave = $this->leaveService->refuseLeave($leave, $request->user()->id, $request->string('reason'));

        return response()->json([
            'message' => 'Congé refusé.',
            'leave'   => new LeaveResource($leave),
        ]);
    }

    public function destroy(Leave $leave): JsonResponse
    {
        $this->authorize('delete', $leave);
        $leave->forceDelete();
        return response()->json(['message' => 'Demande annulée.']);
    }

    /**
     * GET /api/leaves/balance?agent_id=1&year=2026
     */
    public function balance(Request $request): JsonResponse
    {
        $request->validate([
            'agent_id' => ['required', 'exists:agents,id'],
            'year'     => ['required', 'integer'],
        ]);

        $agent   = Agent::findOrFail($request->integer('agent_id'));
        $balance = $this->leaveService->getOrCreateBalance($agent, $request->integer('year'));

        return response()->json([
            'year'              => $balance->year,
            'allocated_days'    => $balance->allocated_days,
            'used_days'         => $balance->used_days,
            'pending_days'      => $balance->pending_days,
            'carried_over_days' => $balance->carried_over_days,
            'remaining_days'    => $balance->remaining_days,
        ]);
    }
}

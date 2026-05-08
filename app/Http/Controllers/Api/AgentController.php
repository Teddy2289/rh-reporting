<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Resources\AgentResource;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class AgentController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Agent::class);

        $query = Agent::with(['department', 'manager', 'user'])
            ->withCount(['planningSlots', 'leaves']);

        // Filtres
        if ($request->filled('department_id')) {
            $query->byDepartment($request->integer('department_id'));
        }
        if ($request->filled('manager_id')) {
            $query->byManager($request->integer('manager_id'));
        }
        if ($request->boolean('active_only', true)) {
            $query->active();
        }
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        // Manager ne voit que son équipe
        if ($request->user()->hasRole('manager') && !$request->user()->hasAnyRole(['admin', 'rh'])) {
            $managerId = $request->user()->agent?->id;
            $query->where('manager_id', $managerId);
        }

        return AgentResource::collection(
            $query->orderBy('last_name')->paginate($request->integer('per_page', 15))
        );
    }

    public function show(Agent $agent): AgentResource
    {
        $this->authorize('view', $agent);
        $agent->load(['department', 'manager', 'user']);
        return new AgentResource($agent);
    }

    public function store(StoreAgentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $agent = DB::transaction(function () use ($data) {
            // Crée le User
            $user = User::create([
                'name'     => "{$data['first_name']} {$data['last_name']}",
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            $user->assignRole('agent');

            // Crée l'Agent
            return Agent::create([
                'user_id'           => $user->id,
                'department_id'     => $data['department_id'],
                'manager_id'        => $data['manager_id'] ?? null,
                'employee_code'     => $data['employee_code'],
                'first_name'        => $data['first_name'],
                'last_name'         => $data['last_name'],
                'phone'             => $data['phone'] ?? null,
                'contract_type'     => $data['contract_type'],
                'hire_date'         => $data['hire_date'],
                'contract_end_date' => $data['contract_end_date'] ?? null,
                'weekly_hours'      => $data['weekly_hours'],
                'annual_leave_days' => $data['annual_leave_days'],
            ]);
        });

        return response()->json(new AgentResource($agent->load(['department', 'user'])), 201);
    }

    public function update(StoreAgentRequest $request, Agent $agent): AgentResource
    {
        $this->authorize('update', $agent);
        $data = $request->validated();

        DB::transaction(function () use ($agent, $data) {
            // Met à jour le User si email ou password fournis
            if (isset($data['email']) || isset($data['password'])) {
                $userUpdate = [];
                if (isset($data['email'])) $userUpdate['email'] = $data['email'];
                if (!empty($data['password'])) $userUpdate['password'] = Hash::make($data['password']);
                $agent->user->update($userUpdate);
            }

            $agent->update(array_filter([
                'department_id'     => $data['department_id'],
                'manager_id'        => $data['manager_id'] ?? null,
                'first_name'        => $data['first_name'],
                'last_name'         => $data['last_name'],
                'phone'             => $data['phone'] ?? null,
                'contract_type'     => $data['contract_type'],
                'hire_date'         => $data['hire_date'],
                'contract_end_date' => $data['contract_end_date'] ?? null,
                'weekly_hours'      => $data['weekly_hours'],
                'annual_leave_days' => $data['annual_leave_days'],
            ], fn($v) => !is_null($v)));
        });

        return new AgentResource($agent->fresh(['department', 'manager', 'user']));
    }

    public function destroy(Agent $agent): JsonResponse
    {
        $this->authorize('delete', $agent);
        $agent->forceDelete();
        return response()->json(['message' => 'Agent supprimé avec succès.']);
    }
}

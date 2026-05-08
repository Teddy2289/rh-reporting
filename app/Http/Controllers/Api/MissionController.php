<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MissionResource;
use App\Models\Client;
use App\Models\Mission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $query = Mission::with('client');
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }
        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }
        return MissionResource::collection($query->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id'   => ['required', 'exists:clients,id'],
            'name'        => ['required', 'string', 'max:200'],
            'code'        => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $mission = Mission::create($data);
        return response()->json(new MissionResource($mission->load('client')), 201);
    }

    public function update(Request $request, Mission $mission): MissionResource
    {
        $data = $request->validate([
            'client_id'   => ['required', 'exists:clients,id'],
            'name'        => ['required', 'string', 'max:200'],
            'code'        => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);
        $mission->update($data);
        return new MissionResource($mission->fresh('client'));
    }

    public function destroy(Mission $mission): JsonResponse
    {
        $mission->forceDelete();
        return response()->json(['message' => 'Mission supprimée.']);
    }
}

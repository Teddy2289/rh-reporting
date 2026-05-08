<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Clients;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $query = Clients::with('missions');
        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }
        return ClientResource::collection($query->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'code'          => ['required', 'string', 'max:20', 'unique:clients'],
            'color'         => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'notes'         => ['nullable', 'string'],
        ]);

        $Clients = Clients::create($data);
        return response()->json(new ClientResource($Clients), 201);
    }

    public function update(Request $request, Clients $Clients): ClientResource
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'code'          => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('clients')->ignore($Clients->id)],
            'color'         => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'is_active'     => ['boolean'],
        ]);
        $Clients->update($data);
        return new ClientResource($Clients->load('missions'));
    }

    public function destroy(Clients $Clients): JsonResponse
    {
        $Clients->forceDelete();
        return response()->json(['message' => 'Clients supprimé.']);
    }
}

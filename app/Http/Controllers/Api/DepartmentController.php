<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    use AuthorizesRequests;

    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return DepartmentResource::collection(
            Department::withCount('agents')->orderBy('name')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Department::class);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:20', 'unique:departments'],
            'color'       => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        // S'assurer que is_active a une valeur par défaut
        $data['is_active'] = $data['is_active'] ?? true;

        $department = Department::create($data);

        return response()->json(new DepartmentResource($department), 201);
    }

    public function update(Request $request, Department $department): DepartmentResource
    {
        $this->authorize('update', $department);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:20', Rule::unique('departments')->ignore($department->id)],
            'color'       => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        $department->update($data);

        return new DepartmentResource($department->loadCount('agents'));
    }

    public function destroy(Department $department): JsonResponse
    {
        $this->authorize('delete', $department);

        // Vérifier si le département a des agents
        if ($department->agents()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer un département qui contient des agents. Veuillez d\'abord réaffecter ou supprimer les agents associés.'
            ], 422);
        }

        // Utiliser delete() au lieu de forceDelete() pour SoftDeletes
        $department->delete();

        return response()->json([
            'message' => 'Département supprimé avec succès.'
        ]);
    }

    // Ajouter une méthode pour restaurer un département soft-deleted
    public function restore($id): JsonResponse
    {
        $department = Department::withTrashed()->findOrFail($id);
        $this->authorize('update', $department);

        $department->restore();

        return response()->json([
            'message' => 'Département restauré avec succès.',
            'data' => new DepartmentResource($department->loadCount('agents'))
        ]);
    }

    // Méthode pour supprimer définitivement (si nécessaire)
    public function forceDelete($id): JsonResponse
    {
        $department = Department::withTrashed()->findOrFail($id);
        $this->authorize('delete', $department);

        // Vérifier si le département a des agents (même soft-deleted)
        if ($department->agents()->withTrashed()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer définitivement un département qui contient des agents.'
            ], 422);
        }

        $department->forceDelete();

        return response()->json([
            'message' => 'Département supprimé définitivement.'
        ]);
    }
}

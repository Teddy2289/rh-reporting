<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
        ]);

        $department = Department::create($data);
        return response()->json(new DepartmentResource($department), 201);
    }

    public function update(Request $request, Department $department): DepartmentResource
    {
        $this->authorize('update', $department);
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'code'        => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('departments')->ignore($department->id)],
            'color'       => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);
        $department->update($data);
        return new DepartmentResource($department);
    }

    public function destroy(Department $department): JsonResponse
    {
        $this->authorize('delete', $department);
        $department->forceDelete();
        return response()->json(['message' => 'Département supprimé.']);
    }
}

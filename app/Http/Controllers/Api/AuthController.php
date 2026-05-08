<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Authentification et génération du Token JWT
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // On tente de générer un token avec le guard 'api' (JWT)
        if (!$token = auth('api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Déconnexion (Invalider le token)
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    /**
     * Récupérer les infos de l'utilisateur connecté
     */
    public function me(): JsonResponse
    {
        $user = auth('api')->user()->load('agent.department');

        return response()->json($this->formatUserResponse($user));
    }

    /**
     * Formatage de la réponse Token
     */
    protected function respondWithToken($token): JsonResponse
    {
        $user = auth('api')->user();

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60, // En secondes
            'user'         => $this->formatUserResponse($user)
        ]);
    }

    /**
     * Helper pour formater les données utilisateur (évite la répétition)
     */
    private function formatUserResponse($user): array
    {
        return [
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'agent'       => $user->agent ? [
                'id'          => $user->agent->id,
                'full_name'   => $user->agent->full_name,
                'avatar_url'  => $user->agent->avatar_url,
                'department'  => $user->agent->department?->name,
                'manager_id'  => $user->agent->manager_id,
            ] : null,
        ];
    }
}

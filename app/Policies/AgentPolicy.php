<?php

namespace App\Policies;

use App\Models\Agent;
use App\Models\User;

class AgentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'rh', 'manager']);
    }

    public function view(User $user, Agent $agent): bool
    {
        if ($user->hasAnyRole(['admin', 'rh'])) return true;

        // Manager ne voit que son équipe
        if ($user->hasRole('manager')) {
            return $user->agent?->id === $agent->manager_id
                || $user->agent?->id === $agent->id;
        }

        // Agent ne voit que son propre profil
        return $user->agent?->id === $agent->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'rh']);
    }

    public function update(User $user, Agent $agent): bool
    {
        return $user->hasAnyRole(['admin', 'rh']);
    }

    public function delete(User $user, Agent $agent): bool
    {
        return $user->hasRole('admin');
    }
}

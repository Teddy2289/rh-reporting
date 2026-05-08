<?php

namespace App\Policies;

use App\Models\PlanningSlot;
use App\Models\User;

class PlanningSlotPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Tout le monde peut voir le planning
    }

    public function view(User $user, PlanningSlot $slot): bool
    {
        if ($user->hasAnyRole(['admin', 'rh'])) return true;
        if ($user->hasRole('manager')) {
            return $user->agent?->id === $slot->agent->manager_id
                || $user->agent?->id === $slot->agent_id;
        }
        return $user->agent?->id === $slot->agent_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'rh', 'manager']);
    }

    public function update(User $user, PlanningSlot $slot): bool
    {
        if ($user->hasAnyRole(['admin', 'rh'])) return true;
        if ($user->hasRole('manager')) {
            return $user->agent?->id === $slot->agent->manager_id;
        }
        return false;
    }

    public function delete(User $user, PlanningSlot $slot): bool
    {
        return $user->hasAnyRole(['admin', 'rh', 'manager']);
    }
}

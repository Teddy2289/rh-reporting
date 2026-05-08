<?php

namespace App\Policies;

use App\Models\Leave;
use App\Models\User;

class LeavePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Leave $leave): bool
    {
        if ($user->hasAnyRole(['admin', 'rh'])) return true;
        if ($user->hasRole('manager')) {
            return $user->agent?->id === $leave->agent->manager_id
                || $user->agent?->id === $leave->agent_id;
        }
        return $user->agent?->id === $leave->agent_id;
    }

    public function create(User $user): bool
    {
        return true; // Tout agent peut faire une demande
    }

    public function update(User $user, Leave $leave): bool
    {
        // Seul l'agent peut modifier sa demande si elle est encore "pending"
        if ($user->agent?->id === $leave->agent_id && $leave->isPending()) return true;
        return $user->hasAnyRole(['admin', 'rh']);
    }

    public function approve(User $user, Leave $leave): bool
    {
        return $user->hasAnyRole(['admin', 'rh', 'manager']);
    }

    public function delete(User $user, Leave $leave): bool
    {
        if ($user->agent?->id === $leave->agent_id && $leave->isPending()) return true;
        return $user->hasRole('admin');
    }
}

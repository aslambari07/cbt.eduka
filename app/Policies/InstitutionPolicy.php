<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Institution;
use App\Models\User;

class InstitutionPolicy
{
    public function view(User $user, Institution $institution): bool
    {
        return $user->isActive() && ($user->hasRole(Role::ADMIN) || $user->institution_id === $institution->id);
    }

    public function update(User $user, Institution $institution): bool
    {
        return $user->isActive() && $user->hasRole(Role::ADMIN);
    }
}

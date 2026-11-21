<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Subject;
use App\Models\User;

class SubjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive() && $user->hasRole([Role::ADMIN, Role::TEACHER]);
    }

    public function create(User $user): bool
    {
        return $user->isActive() && $user->hasRole(Role::ADMIN);
    }

    public function view(User $user, Subject $subject): bool
    {
        return $user->isActive() && ($user->hasRole(Role::ADMIN) || $user->institution_id === $subject->institution_id);
    }

    public function update(User $user, Subject $subject): bool
    {
        return $user->isActive() && ($user->hasRole(Role::ADMIN) || ($user->hasRole(Role::TEACHER) && $user->institution_id === $subject->institution_id));
    }

    public function delete(User $user, Subject $subject): bool
    {
        return $user->isActive() && $user->hasRole(Role::ADMIN);
    }
}

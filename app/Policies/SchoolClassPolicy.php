<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\SchoolClass;
use App\Models\User;

class SchoolClassPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive() && $user->hasRole([Role::ADMIN, Role::TEACHER, Role::STUDENT]);
    }

    public function view(User $user, SchoolClass $class): bool
    {
        return $this->canAccess($user, $class);
    }

    public function update(User $user, SchoolClass $class): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        return $user->hasRole(Role::ADMIN) || ($user->hasRole(Role::TEACHER) && $class->teachers()->whereKey($user->id)->exists());
    }

    public function delete(User $user, SchoolClass $class): bool
    {
        return $user->hasRole(Role::ADMIN) && $user->isActive();
    }

    private function canAccess(User $user, SchoolClass $class): bool
    {
        if (! $user->isActive() || $user->institution_id !== $class->institution_id) {
            return false;
        }

        return $user->hasRole(Role::ADMIN)
            || ($user->hasRole(Role::TEACHER) && $class->teachers()->whereKey($user->id)->exists())
            || ($user->hasRole(Role::STUDENT) && $class->students()->whereKey($user->id)->exists());
    }
}

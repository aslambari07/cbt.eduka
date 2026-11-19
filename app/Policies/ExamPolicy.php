<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Exam;
use App\Models\User;

class ExamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive() && $user->hasRole([Role::ADMIN, Role::TEACHER]);
    }

    public function view(User $user, Exam $exam): bool
    {
        return $this->ownsOrManages($user, $exam);
    }

    public function create(User $user): bool
    {
        return $user->isActive() && $user->hasRole([Role::ADMIN, Role::TEACHER]);
    }

    public function update(User $user, Exam $exam): bool
    {
        return $this->ownsOrManages($user, $exam);
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $this->ownsOrManages($user, $exam) && $exam->status->value === 'draft';
    }

    private function ownsOrManages(User $user, Exam $exam): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        return $user->hasRole(Role::ADMIN)
            || ($user->hasRole(Role::TEACHER) && $exam->owner_id === $user->id && $exam->institution_id === $user->institution_id);
    }
}

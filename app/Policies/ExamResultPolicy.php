<?php

namespace App\Policies;

use App\Enums\ExamResultStatus;
use App\Enums\Role;
use App\Models\ExamResult;
use App\Models\User;

class ExamResultPolicy
{
    public function view(User $user, ExamResult $result): bool
    {
        if (! $user->isActive()) {
            return false;
        }
        if ($user->hasRole(Role::ADMIN) || ($user->hasRole(Role::TEACHER) && $result->exam?->owner_id === $user->id)) {
            return true;
        }

        return $user->hasRole(Role::STUDENT)
            && $result->student_id === $user->id
            && ($result->status === ExamResultStatus::PUBLISHED || ($result->status === ExamResultStatus::GRADED && $result->exam?->results_visibility === 'immediate'));
    }

    public function publish(User $user, ExamResult $result): bool
    {
        return $user->isActive() && ($user->hasRole(Role::ADMIN) || ($user->hasRole(Role::TEACHER) && $result->exam?->owner_id === $user->id)) && $result->status === ExamResultStatus::GRADED;
    }

    public function grade(User $user, ExamResult $result): bool
    {
        return $user->isActive() && ($user->hasRole(Role::ADMIN) || ($user->hasRole(Role::TEACHER) && $result->exam?->owner_id === $user->id));
    }
}

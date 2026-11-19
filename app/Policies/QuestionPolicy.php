<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive() && $user->hasRole([Role::ADMIN, Role::TEACHER]);
    }

    public function view(User $user, Question $question): bool
    {
        return $this->ownsOrManages($user, $question);
    }

    public function create(User $user): bool
    {
        return $user->isActive() && $user->hasRole([Role::ADMIN, Role::TEACHER]);
    }

    public function update(User $user, Question $question): bool
    {
        return $this->ownsOrManages($user, $question);
    }

    public function delete(User $user, Question $question): bool
    {
        return $this->ownsOrManages($user, $question);
    }

    private function ownsOrManages(User $user, Question $question): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        return $user->hasRole(Role::ADMIN)
            || ($user->hasRole(Role::TEACHER) && $question->author_id === $user->id && $question->subject?->institution_id === $user->institution_id);
    }
}

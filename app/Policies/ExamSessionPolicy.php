<?php

namespace App\Policies;

use App\Models\ExamSession;
use App\Models\User;

class ExamSessionPolicy
{
    public function view(User $user, ExamSession $session): bool
    {
        return $user->isActive() && $user->hasRole('student') && (int) $session->student_id === (int) $user->id;
    }

    public function update(User $user, ExamSession $session): bool
    {
        return $this->view($user, $session);
    }
}

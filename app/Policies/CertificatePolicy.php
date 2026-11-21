<?php

namespace App\Policies;

use App\Models\Certificate;
use App\Models\User;

class CertificatePolicy
{
    public function view(User $user, Certificate $certificate): bool
    {
        return $user->isActive() && ($user->id === $certificate->student_id || $user->hasRole('admin'));
    }
}

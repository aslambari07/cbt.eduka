<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AccountStatus;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'institution_id', 'role', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'status' => AccountStatus::class,
        ];
    }

    public function hasRole(Role|array|string $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return collect($roles)->contains(fn ($role) => $this->role === ($role instanceof Role ? $role : Role::tryFrom((string) $role)));
    }

    public function isActive(): bool
    {
        return $this->status === AccountStatus::ACTIVE;
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function teacherProfile()
    {
        return $this->hasOne(TeacherProfile::class);
    }

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_members', 'student_id', 'class_id')->withPivot('joined_at')->withTimestamps();
    }

    public function taughtClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'teacher_assignments', 'teacher_id', 'class_id')->withPivot('subject_id')->withTimestamps();
    }

    public function assignedSubjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_assignments', 'teacher_id', 'subject_id')->withPivot('class_id')->withTimestamps();
    }

    public function ownedExams()
    {
        return $this->hasMany(Exam::class, 'owner_id');
    }

    public function examAssignments()
    {
        return $this->hasMany(ExamAssignment::class, 'student_id');
    }
}

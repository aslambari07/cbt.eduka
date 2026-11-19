<?php

namespace App\Models;

use App\Enums\ExamAssignmentStatus;
use Illuminate\Database\Eloquent\Model;

class ExamAssignment extends Model
{
    protected $fillable = ['exam_id', 'student_id', 'status', 'max_attempts', 'assigned_at'];

    protected function casts(): array
    {
        return ['status' => ExamAssignmentStatus::class, 'assigned_at' => 'datetime'];
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}

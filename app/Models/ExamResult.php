<?php

namespace App\Models;

use App\Enums\ExamResultStatus;
use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    protected $fillable = ['exam_session_id', 'exam_id', 'student_id', 'status', 'total_points', 'earned_points', 'score', 'passing_grade', 'is_passed', 'graded_at', 'published_at', 'metadata'];

    protected function casts(): array
    {
        return ['status' => ExamResultStatus::class, 'total_points' => 'decimal:2', 'earned_points' => 'decimal:2', 'score' => 'decimal:2', 'passing_grade' => 'decimal:2', 'is_passed' => 'boolean', 'graded_at' => 'datetime', 'published_at' => 'datetime', 'metadata' => 'array'];
    }

    public function session()
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function details()
    {
        return $this->hasMany(ExamResultDetail::class);
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class);
    }
}

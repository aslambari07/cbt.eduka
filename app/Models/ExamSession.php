<?php

namespace App\Models;

use App\Enums\ExamSessionStatus;
use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    protected $fillable = [
        'session_key', 'exam_id', 'assignment_id', 'student_id', 'attempt_number', 'status', 'started_at',
        'expires_at', 'submitted_at', 'last_seen_at', 'version', 'question_order', 'option_orders',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExamSessionStatus::class,
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'question_order' => 'array',
            'option_orders' => 'array',
        ];
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function assignment()
    {
        return $this->belongsTo(ExamAssignment::class, 'assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers()
    {
        return $this->hasMany(ExamAnswer::class);
    }

    public function flags()
    {
        return $this->hasMany(ExamFlag::class);
    }

    public function events()
    {
        return $this->hasMany(ExamEvent::class);
    }
}

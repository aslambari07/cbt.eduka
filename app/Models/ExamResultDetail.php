<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResultDetail extends Model
{
    protected $fillable = ['exam_result_id', 'question_id', 'exam_answer_id', 'max_points', 'earned_points', 'grading_status', 'feedback', 'graded_by', 'graded_at', 'metadata'];

    protected function casts(): array
    {
        return ['max_points' => 'decimal:2', 'earned_points' => 'decimal:2', 'graded_at' => 'datetime', 'metadata' => 'array'];
    }

    public function result()
    {
        return $this->belongsTo(ExamResult::class, 'exam_result_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answer()
    {
        return $this->belongsTo(ExamAnswer::class, 'exam_answer_id');
    }

    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAnswer extends Model
{
    protected $fillable = ['exam_session_id', 'question_id', 'answer', 'version', 'status', 'grading_status', 'score', 'feedback', 'saved_at'];

    protected function casts(): array
    {
        return ['answer' => 'array', 'score' => 'decimal:2', 'saved_at' => 'datetime'];
    }

    public function session()
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}

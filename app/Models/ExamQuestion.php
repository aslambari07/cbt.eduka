<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    protected $fillable = ['exam_id', 'question_id', 'sort_order', 'points', 'required'];

    protected function casts(): array
    {
        return ['points' => 'decimal:2', 'required' => 'boolean'];
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamEvent extends Model
{
    protected $fillable = ['exam_session_id', 'actor_id', 'type', 'payload', 'occurred_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'occurred_at' => 'datetime'];
    }

    public function session()
    {
        return $this->belongsTo(ExamSession::class, 'exam_session_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}

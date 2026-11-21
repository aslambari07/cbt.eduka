<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = ['exam_result_id', 'student_id', 'certificate_number', 'status', 'issued_at', 'revoked_at', 'pdf_path', 'metadata'];

    protected function casts(): array
    {
        return ['issued_at' => 'datetime', 'revoked_at' => 'datetime', 'metadata' => 'array'];
    }

    public function result()
    {
        return $this->belongsTo(ExamResult::class, 'exam_result_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}

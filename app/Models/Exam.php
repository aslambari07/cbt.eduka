<?php

namespace App\Models;

use App\Enums\ExamStatus;
use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    /** @use HasFactory<ExamFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id', 'institution_id', 'subject_id', 'class_id', 'name', 'code', 'description', 'instructions',
        'duration_minutes', 'starts_at', 'ends_at', 'total_points', 'passing_grade', 'max_attempts',
        'randomize_questions', 'randomize_options', 'navigation_mode', 'results_visibility', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExamStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'total_points' => 'decimal:2',
            'passing_grade' => 'decimal:2',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
            ->withPivot(['sort_order', 'points', 'required'])
            ->orderBy('exam_questions.sort_order');
    }

    public function assignments()
    {
        return $this->hasMany(ExamAssignment::class);
    }

    public function sessions()
    {
        return $this->hasMany(ExamSession::class);
    }

    public function isOpenAt(?\DateTimeInterface $at = null): bool
    {
        $at = $at ? now()->setTimestamp($at->getTimestamp()) : now();

        return $this->status === ExamStatus::ONGOING
            && (! $this->starts_at || $at->greaterThanOrEqualTo($this->starts_at))
            && (! $this->ends_at || $at->lessThan($this->ends_at));
    }
}

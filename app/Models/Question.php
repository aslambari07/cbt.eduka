<?php

namespace App\Models;

use App\Enums\QuestionType;
use App\Services\QuestionTypeRegistry;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['author_id', 'subject_id', 'category_id', 'type', 'prompt', 'explanation', 'definition', 'points', 'difficulty', 'status'];

    protected function casts(): array
    {
        return ['type' => QuestionType::class, 'definition' => 'array', 'points' => 'decimal:2'];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function category()
    {
        return $this->belongsTo(QuestionCategory::class, 'category_id');
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('sort_order');
    }

    public function publicRenderData(): array
    {
        return app(QuestionTypeRegistry::class)->for($this->type)->renderData($this, false);
    }

    public function renderDataWithKey(): array
    {
        return app(QuestionTypeRegistry::class)->for($this->type)->renderData($this, true);
    }
}

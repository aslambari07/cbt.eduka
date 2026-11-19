<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionTag extends Model
{
    use HasFactory;

    protected $fillable = ['institution_id', 'name', 'slug'];

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_tag');
    }
}

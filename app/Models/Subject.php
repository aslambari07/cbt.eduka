<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['institution_id', 'name', 'code', 'description', 'status'];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subjects', 'subject_id', 'class_id')->withTimestamps();
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_assignments', 'subject_id', 'teacher_id')->withPivot('class_id')->withTimestamps();
    }
}

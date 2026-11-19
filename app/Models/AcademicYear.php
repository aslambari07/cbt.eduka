<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = ['institution_id', 'name', 'code', 'is_current'];

    protected function casts(): array
    {
        return ['is_current' => 'boolean'];
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}

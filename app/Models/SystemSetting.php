<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['setting_key', 'value', 'is_sensitive'];

    protected function casts(): array
    {
        return ['value' => 'array', 'is_sensitive' => 'boolean'];
    }
}

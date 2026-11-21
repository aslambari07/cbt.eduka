<?php

namespace App\Http\Requests;

use App\Models\SchoolClass;
use Illuminate\Foundation\Http\FormRequest;

class SchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('class') ? 'update' : 'create', $this->route('class') ?? SchoolClass::class);
    }

    public function rules(): array
    {
        return ['institution_id' => ['required', 'integer', 'exists:institutions,id'], 'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'], 'homeroom_teacher_id' => ['nullable', 'integer', 'exists:users,id'], 'name' => ['required', 'string', 'max:120'], 'level' => ['nullable', 'string', 'max:30'], 'status' => ['required', 'string', 'max:30']];
    }
}

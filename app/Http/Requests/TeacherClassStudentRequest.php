<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherClassStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('class')) === true;
    }

    public function rules(): array
    {
        return ['student_id' => ['required', 'integer', 'exists:users,id']];
    }
}

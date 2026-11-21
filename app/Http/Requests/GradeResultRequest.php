<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole(['admin', 'teacher']);
    }

    public function rules(): array
    {
        return ['score' => ['required', 'numeric', 'min:0'], 'feedback' => ['nullable', 'string', 'max:5000']];
    }
}

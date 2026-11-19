<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveExamAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole('student');
    }

    public function rules(): array
    {
        return [
            'answer' => ['present'],
            'client_version' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

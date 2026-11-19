<?php

namespace App\Http\Requests;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;

class QuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $question = $this->route('question');

        return $question instanceof Question
            ? $this->user()?->can('update', $question) === true
            : $this->user()?->can('create', Question::class) === true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'category_id' => ['nullable', 'integer', 'exists:question_categories,id'],
            'type' => ['required', 'string', 'in:multiple_choice,multiple_select,true_false,short_answer,essay,matching,ordering,fill_blank'],
            'prompt' => ['required', 'string', 'min:5'],
            'explanation' => ['nullable', 'string'],
            'definition' => ['nullable', 'array'],
            'points' => ['required', 'numeric', 'min:0.01', 'max:1000'],
            'difficulty' => ['required', 'in:easy,medium,hard'],
            'status' => ['required', 'in:draft,published,archived'],
            'options' => ['nullable', 'array'],
            'options.*.key' => ['required_with:options', 'string', 'max:10'],
            'options.*.label' => ['required_with:options', 'string'],
            'options.*.is_correct' => ['sometimes', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Services\AiQuestionValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

class AiQuestionStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $questions = $this->input('questions');

        if (is_string($questions)) {
            $decoded = json_decode($questions, true);
            $questions = is_array($decoded) ? $decoded : [];
        }

        $this->merge(['questions' => $questions]);
    }

    public function authorize(): bool
    {
        return $this->user()?->isActive() === true
            && $this->user()->hasRole(['admin', 'teacher'])
            && $this->user()->can('create', Question::class);
    }

    public function rules(): array
    {
        return [
            'questions' => ['required', 'array', 'min:1', 'max:'.(int) config('cbt.ai.max_questions', 20)],
            'questions.*' => ['required', 'array'],
            'questions.*.subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'questions.*.category_id' => ['nullable', 'integer', 'exists:question_categories,id'],
            'questions.*.type' => ['required', Rule::enum(QuestionType::class)],
            'questions.*.prompt' => ['required', 'string'],
            'questions.*.options' => ['required', 'array'],
            'questions.*.definition' => ['required', 'array'],
            'questions.*.explanation' => ['required', 'string'],
            'questions.*.points' => ['required', 'numeric'],
            'questions.*.difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            try {
                app(AiQuestionValidator::class)->validate(['questions' => $this->input('questions', [])], count($this->input('questions', [])));
            } catch (InvalidArgumentException $exception) {
                $validator->errors()->add('questions', $exception->getMessage());
            }
        }];
    }
}

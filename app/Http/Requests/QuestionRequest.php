<?php

namespace App\Http\Requests;

use App\Enums\QuestionType;
use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'options.*.key' => ['required_with:options', 'string', 'max:10', 'distinct'],
            'options.*.label' => ['required_with:options', 'string'],
            'options.*.is_correct' => ['sometimes', 'boolean'],
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['type', 'options', 'definition'])) {
                return;
            }

            $type = QuestionType::tryFrom((string) $this->input('type'));
            $options = $this->input('options', []);
            $correctCount = collect($options)->filter(fn (array $option): bool => (bool) ($option['is_correct'] ?? false))->count();
            $definition = $this->input('definition', []);

            if (in_array($type, [QuestionType::MULTIPLE_CHOICE, QuestionType::MULTIPLE_SELECT], true) && count($options) < 2) {
                $validator->errors()->add('options', 'Tambahkan minimal dua opsi untuk soal pilihan.');
            }
            if ($type === QuestionType::MULTIPLE_CHOICE && $correctCount !== 1) {
                $validator->errors()->add('options', 'Pilihan ganda harus memiliki tepat satu jawaban benar.');
            }
            if ($type === QuestionType::MULTIPLE_SELECT && $correctCount < 1) {
                $validator->errors()->add('options', 'Multiple select harus memiliki minimal satu jawaban benar.');
            }
            if ($type === QuestionType::TRUE_FALSE && ! array_key_exists('correct', $definition)) {
                $validator->errors()->add('definition.correct', 'Jawaban benar/salah wajib dipilih.');
            }
            if ($type === QuestionType::SHORT_ANSWER && collect($definition['accepted_answers'] ?? [])->filter(fn ($answer) => filled($answer))->isEmpty()) {
                $validator->errors()->add('definition.accepted_answers', 'Minimal satu jawaban singkat yang diterima harus diisi.');
            }
            if ($type === QuestionType::MATCHING && collect($definition['pairs'] ?? [])->filter(fn ($pair) => filled($pair['left'] ?? null) && filled($pair['right'] ?? null))->isEmpty()) {
                $validator->errors()->add('definition.pairs', 'Pasangan matching wajib diisi.');
            }
            if ($type === QuestionType::ORDERING && collect($definition['correct_order'] ?? [])->filter(fn ($item) => filled($item))->count() < 2) {
                $validator->errors()->add('definition.correct_order', 'Urutan jawaban wajib memiliki minimal dua item.');
            }
            if ($type === QuestionType::FILL_BLANK && collect($definition['blanks'] ?? [])->filter(fn ($blank) => filled($blank))->isEmpty()) {
                $validator->errors()->add('definition.blanks', 'Minimal satu jawaban isian harus diisi.');
            }
        }];
    }
}

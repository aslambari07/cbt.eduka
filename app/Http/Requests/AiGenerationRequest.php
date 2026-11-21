<?php

namespace App\Http\Requests;

use App\Enums\QuestionType;
use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AiGenerationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'topic' => $this->input('topic', $this->input('subject')),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole(['admin', 'teacher']);
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:160'],
            'topic' => ['required', 'string', 'max:300'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
            'count' => ['required', 'integer', 'min:1', 'max:'.(int) config('cbt.ai.max_questions', 20)],
            'type' => ['required', Rule::enum(QuestionType::class)],
            'language' => ['required', 'string', 'max:40'],
            'grade' => ['required', 'string', 'max:40'],
            'instructions' => ['nullable', 'string', 'max:1500'],
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty() || ! $this->filled('subject_id')) {
                return;
            }

            $subject = Subject::query()->find((int) $this->input('subject_id'));

            if (! $subject || (! $this->user()->hasRole('admin') && $subject->institution_id !== $this->user()->institution_id)) {
                $validator->errors()->add('subject_id', 'Mata pelajaran tidak dapat digunakan oleh akun ini.');
            }

            $materialLength = mb_strlen((string) $this->input('description', '')) + mb_strlen((string) $this->input('instructions', ''));

            if ($materialLength > (int) config('cbt.ai.max_material_chars', 10000)) {
                $validator->errors()->add('description', 'Materi dan instruksi terlalu panjang untuk diproses AI.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'count.max' => 'Jumlah soal maksimal :max per permintaan.',
            'type.enum' => 'Tipe soal belum didukung oleh generator AI.',
        ];
    }
}

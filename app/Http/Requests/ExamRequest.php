<?php

namespace App\Http\Requests;

use App\Models\Exam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        $exam = $this->route('exam');

        return $exam instanceof Exam
            ? $this->user()->can('update', $exam)
            : $this->user()->can('create', Exam::class);
    }

    public function rules(): array
    {
        $exam = $this->route('exam');

        return [
            'name' => ['required', 'string', 'max:160'],
            'code' => ['required', 'string', 'max:80', Rule::unique('exams', 'code')->ignore($exam?->id)],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'instructions' => ['nullable', 'string', 'max:5000'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'total_points' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'passing_grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'randomize_questions' => ['nullable', 'boolean'],
            'randomize_options' => ['nullable', 'boolean'],
            'navigation_mode' => ['required', Rule::in(['free', 'linear'])],
            'results_visibility' => ['required', Rule::in(['after_review', 'immediate', 'hidden'])],
            'question_ids' => ['nullable', 'array'],
            'question_ids.*' => ['integer', 'distinct', 'exists:questions,id'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'randomize_questions' => $this->boolean('randomize_questions'),
            'randomize_options' => $this->boolean('randomize_options'),
            'navigation_mode' => $this->input('navigation_mode', 'free'),
            'results_visibility' => $this->input('results_visibility', 'after_review'),
            'max_attempts' => $this->input('max_attempts', 1),
        ]);
    }
}

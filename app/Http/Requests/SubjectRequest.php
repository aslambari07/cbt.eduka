<?php

namespace App\Http\Requests;

use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('subject') ? 'update' : 'create', $this->route('subject') ?? Subject::class);
    }

    public function rules(): array
    {
        $subject = $this->route('subject');

        return ['institution_id' => ['required', 'integer', 'exists:institutions,id'], 'name' => ['required', 'string', 'max:160'], 'code' => ['required', 'string', 'max:40', Rule::unique('subjects', 'code')->ignore($subject?->id)], 'description' => ['nullable', 'string', 'max:2000'], 'status' => ['required', 'string', 'max:30']];
    }
}

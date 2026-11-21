<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SystemSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') === true && $this->user()->isActive();
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'platform_name' => ['required', 'string', 'max:120'],
            'platform_tagline' => ['required', 'string', 'max:255'],
            'default_duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'default_passing_grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'results_publication' => ['required', 'in:after_review,immediate,hidden'],
            'maintenance_mode' => ['sometimes', 'boolean'],
        ];
    }
}

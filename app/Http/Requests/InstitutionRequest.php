<?php

namespace App\Http\Requests;

use App\Models\Institution;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstitutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('institution') ? 'update' : 'create', $this->route('institution') ?? Institution::class);
    }

    public function rules(): array
    {
        $institution = $this->route('institution');

        return ['name' => ['required', 'string', 'max:160'], 'code' => ['required', 'string', 'max:40', Rule::unique('institutions', 'code')->ignore($institution?->id)], 'contact_email' => ['nullable', 'email'], 'address' => ['nullable', 'string', 'max:1000'], 'status' => ['required', 'string', 'max:30']];
    }
}

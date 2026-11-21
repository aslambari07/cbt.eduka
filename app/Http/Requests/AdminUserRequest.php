<?php

namespace App\Http\Requests;

use App\Enums\AccountStatus;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('user') ? 'update' : 'create', $this->route('user') ?? User::class);
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return ['name' => ['required', 'string', 'max:120'], 'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)], 'role' => ['required', Rule::in(array_column(Role::cases(), 'value'))], 'status' => ['required', Rule::in(array_column(AccountStatus::cases(), 'value'))], 'institution_id' => ['nullable', 'integer', 'exists:institutions,id'], 'password' => [$user ? 'nullable' : 'required', 'string', 'min:8']];
    }
}

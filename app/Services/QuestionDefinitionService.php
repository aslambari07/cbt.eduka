<?php

namespace App\Services;

use App\Enums\QuestionType;

class QuestionDefinitionService
{
    public function build(array $data): array
    {
        $definition = $data['definition'] ?? [];
        $options = $data['options'] ?? [];
        $type = QuestionType::from($data['type']);

        if ($type === QuestionType::MULTIPLE_CHOICE) {
            $definition['correct_option'] = collect($options)->first(fn (array $option) => (bool) ($option['is_correct'] ?? false))['key'] ?? null;
        }

        if ($type === QuestionType::MULTIPLE_SELECT) {
            $definition['correct_options'] = collect($options)->filter(fn (array $option) => (bool) ($option['is_correct'] ?? false))->pluck('key')->values()->all();
        }

        return $definition;
    }
}

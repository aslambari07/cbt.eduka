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

        if ($type === QuestionType::TRUE_FALSE && array_key_exists('correct', $definition)) {
            $definition['correct'] = filter_var($definition['correct'], FILTER_VALIDATE_BOOLEAN);
        }

        if ($type === QuestionType::SHORT_ANSWER) {
            $definition['accepted_answers'] = collect($definition['accepted_answers'] ?? [])->map(fn ($answer) => trim((string) $answer))->filter()->values()->all();
        }

        if ($type === QuestionType::MATCHING) {
            $definition['pairs'] = collect($definition['pairs'] ?? [])->filter(fn ($pair) => filled($pair['left'] ?? null) && filled($pair['right'] ?? null))->mapWithKeys(fn ($pair) => [(string) $pair['left'] => (string) $pair['right']])->all();
        }

        if ($type === QuestionType::ORDERING) {
            $definition['correct_order'] = collect($definition['correct_order'] ?? [])->map(fn ($item) => trim((string) $item))->filter()->values()->all();
        }

        if ($type === QuestionType::FILL_BLANK) {
            $definition['blanks'] = collect($definition['blanks'] ?? [])->map(function ($blank): array {
                $answers = is_array($blank) ? $blank : explode(',', (string) $blank);

                return collect($answers)->map(fn ($answer) => trim((string) $answer))->filter()->values()->all();
            })->filter(fn (array $answers): bool => $answers !== [])->values()->all();
        }

        return $definition;
    }
}

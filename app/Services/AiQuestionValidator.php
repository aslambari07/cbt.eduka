<?php

namespace App\Services;

use App\Enums\QuestionType;
use InvalidArgumentException;

class AiQuestionValidator
{
    /**
     * @return array{questions: list<array<string, mixed>>, partial: bool}
     */
    public function validate(array $payload, int $requestedCount): array
    {
        if (array_diff(array_keys($payload), ['questions']) !== [] || ! is_array($payload['questions'] ?? null)) {
            throw new InvalidArgumentException('Response harus memiliki field questions.');
        }

        $questions = $payload['questions'];

        if ($questions === []) {
            throw new InvalidArgumentException('Response tidak berisi soal.');
        }

        if (count($questions) > $requestedCount) {
            throw new InvalidArgumentException('Jumlah soal melebihi jumlah yang diminta.');
        }

        $normalized = [];
        $prompts = [];

        foreach ($questions as $index => $question) {
            if (! is_array($question)) {
                throw new InvalidArgumentException("Soal #{$index} tidak valid.");
            }

            $normalizedQuestion = $this->normalizeQuestion($question, (int) $index);
            $promptKey = $this->normalizeText($normalizedQuestion['prompt']);

            if (in_array($promptKey, $prompts, true)) {
                throw new InvalidArgumentException('Response berisi pertanyaan duplikat.');
            }

            $prompts[] = $promptKey;
            $normalized[] = $normalizedQuestion;
        }

        return [
            'questions' => $normalized,
            'partial' => count($normalized) < $requestedCount,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeQuestion(array $question, int $index): array
    {
        $allowedKeys = ['type', 'prompt', 'options', 'explanation', 'definition', 'points', 'difficulty', 'subject_id', 'category_id'];
        $unknownKeys = array_diff(array_keys($question), $allowedKeys);

        if ($unknownKeys !== []) {
            throw new InvalidArgumentException("Soal #{$index} memiliki field yang tidak didukung.");
        }

        $type = QuestionType::tryFrom((string) ($question['type'] ?? ''));

        if (! $type) {
            throw new InvalidArgumentException("Tipe soal #{$index} tidak didukung.");
        }

        $prompt = $this->requiredText($question['prompt'] ?? null, "Pertanyaan #{$index}", 5, 2000);
        $explanation = $this->requiredText($question['explanation'] ?? null, "Penjelasan #{$index}", 0, 4000);
        $points = $question['points'] ?? null;

        if (! is_numeric($points) || (float) $points < 0.01 || (float) $points > 1000) {
            throw new InvalidArgumentException("Bobot soal #{$index} tidak valid.");
        }

        $difficulty = (string) ($question['difficulty'] ?? '');

        if (! in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
            throw new InvalidArgumentException("Tingkat kesulitan soal #{$index} tidak valid.");
        }

        $options = $this->normalizeOptions($question['options'] ?? null, $type, $index);
        $definition = $this->normalizeDefinition($question['definition'] ?? null, $type, $index, $prompt);

        $normalized = [
            'type' => $type->value,
            'prompt' => $prompt,
            'options' => $options,
            'explanation' => $explanation,
            'definition' => $definition,
            'points' => round((float) $points, 2),
            'difficulty' => $difficulty,
        ];

        foreach (['subject_id', 'category_id'] as $key) {
            if (array_key_exists($key, $question) && $question[$key] !== null) {
                if (! is_numeric($question[$key]) || (int) $question[$key] < 1) {
                    throw new InvalidArgumentException("{$key} pada soal #{$index} tidak valid.");
                }

                $normalized[$key] = (int) $question[$key];
            }
        }

        return $normalized;
    }

    /**
     * @return list<array{key: string, label: string, is_correct: bool}>
     */
    private function normalizeOptions(mixed $value, QuestionType $type, int $index): array
    {
        if (! is_array($value)) {
            throw new InvalidArgumentException("Opsi soal #{$index} tidak valid.");
        }

        $choiceType = in_array($type, [QuestionType::MULTIPLE_CHOICE, QuestionType::MULTIPLE_SELECT], true);

        if (! $choiceType && $value !== []) {
            throw new InvalidArgumentException("Tipe {$type->value} tidak boleh memiliki opsi pilihan.");
        }

        if ($choiceType && count($value) < 2) {
            throw new InvalidArgumentException("Soal pilihan #{$index} membutuhkan minimal dua opsi.");
        }

        $keys = [];
        $labels = [];
        $options = [];

        foreach ($value as $optionIndex => $option) {
            if (! is_array($option) || array_diff(array_keys($option), ['key', 'label', 'is_correct']) !== []) {
                throw new InvalidArgumentException("Opsi #{$optionIndex} pada soal #{$index} tidak valid.");
            }

            $key = strtoupper($this->requiredText($option['key'] ?? null, 'Key opsi', 1, 10));
            $label = $this->requiredText($option['label'] ?? null, 'Label opsi', 1, 500);

            if (in_array($key, $keys, true) || in_array($this->normalizeText($label), $labels, true)) {
                throw new InvalidArgumentException("Opsi soal #{$index} duplikat.");
            }

            if (! is_bool($option['is_correct'] ?? null)) {
                throw new InvalidArgumentException("Status jawaban opsi soal #{$index} tidak valid.");
            }

            $keys[] = $key;
            $labels[] = $this->normalizeText($label);
            $options[] = ['key' => $key, 'label' => $label, 'is_correct' => $option['is_correct']];
        }

        $correctCount = count(array_filter($options, fn (array $option): bool => $option['is_correct']));

        if ($type === QuestionType::MULTIPLE_CHOICE && $correctCount !== 1) {
            throw new InvalidArgumentException("Soal pilihan ganda #{$index} harus memiliki tepat satu jawaban benar.");
        }

        if ($type === QuestionType::MULTIPLE_SELECT && $correctCount < 1) {
            throw new InvalidArgumentException("Soal multiple select #{$index} harus memiliki jawaban benar.");
        }

        return $options;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeDefinition(mixed $value, QuestionType $type, int $index, string $prompt): array
    {
        if (! is_array($value)) {
            throw new InvalidArgumentException("Definition soal #{$index} tidak valid.");
        }

        $allowed = ['correct', 'accepted_answers', 'pairs', 'correct_order', 'blanks', 'rubric', 'partial_scoring'];

        if (array_diff(array_keys($value), $allowed) !== []) {
            throw new InvalidArgumentException("Definition soal #{$index} memiliki field yang tidak didukung.");
        }

        $definition = [
            'correct' => null,
            'accepted_answers' => [],
            'pairs' => [],
            'correct_order' => [],
            'blanks' => [],
            'rubric' => '',
            'partial_scoring' => false,
        ];

        if (array_key_exists('partial_scoring', $value) && ! is_bool($value['partial_scoring'])) {
            throw new InvalidArgumentException("Partial scoring soal #{$index} tidak valid.");
        }

        $definition['partial_scoring'] = $value['partial_scoring'] ?? false;

        if (in_array($type, [QuestionType::MULTIPLE_CHOICE, QuestionType::MULTIPLE_SELECT], true)) {
            return $definition;
        }

        if ($type === QuestionType::TRUE_FALSE) {
            if (! is_bool($value['correct'] ?? null)) {
                throw new InvalidArgumentException("Jawaban true/false soal #{$index} tidak valid.");
            }

            $definition['correct'] = $value['correct'];

            return $definition;
        }

        if ($type === QuestionType::SHORT_ANSWER) {
            $definition['accepted_answers'] = $this->stringList($value['accepted_answers'] ?? null, "Jawaban acuan soal #{$index}", 1, 500);

            return $definition;
        }

        if ($type === QuestionType::ESSAY) {
            $rubric = $value['rubric'] ?? '';

            if (! is_string($rubric) || mb_strlen($rubric) > 2000) {
                throw new InvalidArgumentException("Rubrik esai soal #{$index} tidak valid.");
            }

            $definition['rubric'] = trim($rubric);

            return $definition;
        }

        if ($type === QuestionType::MATCHING) {
            if (! is_array($value['pairs'] ?? null) || count($value['pairs']) < 2) {
                throw new InvalidArgumentException("Pasangan matching soal #{$index} tidak lengkap.");
            }

            $left = [];
            $right = [];

            foreach ($value['pairs'] as $pair) {
                if (! is_array($pair)) {
                    throw new InvalidArgumentException("Pasangan matching soal #{$index} tidak valid.");
                }

                $pairLeft = $this->requiredText($pair['left'] ?? null, 'Sisi kiri pasangan', 1, 500);
                $pairRight = $this->requiredText($pair['right'] ?? null, 'Sisi kanan pasangan', 1, 500);

                if (in_array($this->normalizeText($pairLeft), $left, true) || in_array($this->normalizeText($pairRight), $right, true)) {
                    throw new InvalidArgumentException("Pasangan matching soal #{$index} duplikat.");
                }

                $left[] = $this->normalizeText($pairLeft);
                $right[] = $this->normalizeText($pairRight);
                $definition['pairs'][] = ['left' => $pairLeft, 'right' => $pairRight];
            }

            return $definition;
        }

        if ($type === QuestionType::ORDERING) {
            $definition['correct_order'] = $this->stringList($value['correct_order'] ?? null, "Urutan soal #{$index}", 2, 500);

            if (count(array_unique(array_map([$this, 'normalizeText'], $definition['correct_order']))) !== count($definition['correct_order'])) {
                throw new InvalidArgumentException("Item urutan soal #{$index} duplikat.");
            }

            return $definition;
        }

        if ($type === QuestionType::FILL_BLANK) {
            if (! is_array($value['blanks'] ?? null) || $value['blanks'] === []) {
                throw new InvalidArgumentException("Isian soal #{$index} tidak lengkap.");
            }

            foreach ($value['blanks'] as $blank) {
                $definition['blanks'][] = $this->stringList($blank, "Jawaban isian soal #{$index}", 1, 500);
            }

            preg_match_all('/\[BLANK_\d+\]/i', $this->normalizeText($prompt), $matches);

            if (count($matches[0]) !== count($definition['blanks'])) {
                throw new InvalidArgumentException("Placeholder [BLANK_1] pada soal #{$index} harus sesuai jumlah isian.");
            }
        }

        return $definition;
    }

    /**
     * @return list<string>
     */
    private function stringList(mixed $value, string $label, int $minimum, int $maxLength): array
    {
        if (! is_array($value) || count($value) < $minimum) {
            throw new InvalidArgumentException("{$label} tidak lengkap.");
        }

        $items = [];

        foreach ($value as $item) {
            $text = $this->requiredText($item, $label, 1, $maxLength);
            $items[] = $text;
        }

        return $items;
    }

    private function requiredText(mixed $value, string $label, int $minimum, int $maximum): string
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException("{$label} harus berupa teks.");
        }

        $value = trim($value);

        if (mb_strlen($value) < $minimum || mb_strlen($value) > $maximum) {
            throw new InvalidArgumentException("{$label} memiliki panjang tidak valid.");
        }

        return $value;
    }

    private function normalizeText(string $value): string
    {
        return mb_strtolower((string) preg_replace('/\s+/', ' ', trim($value)));
    }
}

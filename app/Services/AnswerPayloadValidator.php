<?php

namespace App\Services;

use App\Enums\QuestionType;
use App\Models\Question;
use Illuminate\Validation\ValidationException;

class AnswerPayloadValidator
{
    public function validateAndNormalize(Question $question, mixed $answer): mixed
    {
        $type = $question->type;
        $options = $question->relationLoaded('options') ? $question->options : $question->options()->get();
        $keys = $options->pluck('key')->map(fn ($key) => strtoupper((string) $key))->all();

        return match ($type) {
            QuestionType::MULTIPLE_CHOICE => $this->choice($answer, $keys),
            QuestionType::MULTIPLE_SELECT => $this->multipleSelect($answer, $keys),
            QuestionType::TRUE_FALSE => $this->boolean($answer),
            QuestionType::SHORT_ANSWER => $this->text($answer, 2000),
            QuestionType::ESSAY => $this->text($answer, 20000),
            QuestionType::MATCHING, QuestionType::ORDERING, QuestionType::FILL_BLANK => $this->arrayAnswer($answer),
        };
    }

    private function choice(mixed $answer, array $keys): string
    {
        if (! is_string($answer) || ! in_array(strtoupper(trim($answer)), $keys, true)) {
            throw ValidationException::withMessages(['answer' => 'Pilihan jawaban tidak valid.']);
        }

        return strtoupper(trim($answer));
    }

    private function multipleSelect(mixed $answer, array $keys): array
    {
        if (! is_array($answer) || $answer === []) {
            throw ValidationException::withMessages(['answer' => 'Pilih minimal satu jawaban.']);
        }

        $normalized = collect($answer)->map(fn ($value) => strtoupper(trim((string) $value)))->unique()->values()->all();
        if (collect($normalized)->diff($keys)->isNotEmpty()) {
            throw ValidationException::withMessages(['answer' => 'Salah satu pilihan jawaban tidak valid.']);
        }

        return $normalized;
    }

    private function boolean(mixed $answer): bool
    {
        $value = filter_var($answer, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($value === null) {
            throw ValidationException::withMessages(['answer' => 'Jawaban benar/salah tidak valid.']);
        }

        return $value;
    }

    private function text(mixed $answer, int $maxLength): string
    {
        if (! is_string($answer) || mb_strlen($answer) > $maxLength) {
            throw ValidationException::withMessages(['answer' => 'Format atau panjang jawaban tidak valid.']);
        }

        return trim($answer);
    }

    private function arrayAnswer(mixed $answer): array
    {
        if (! is_array($answer) || count($answer) > 500) {
            throw ValidationException::withMessages(['answer' => 'Format jawaban tidak valid.']);
        }

        return $answer;
    }
}

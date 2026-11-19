<?php

namespace App\Services;

use App\Contracts\QuestionTypeHandler;
use App\Enums\QuestionType;
use App\QuestionTypes\EssayHandler;
use App\QuestionTypes\FillBlankHandler;
use App\QuestionTypes\MatchingHandler;
use App\QuestionTypes\MultipleChoiceHandler;
use App\QuestionTypes\MultipleSelectHandler;
use App\QuestionTypes\OrderingHandler;
use App\QuestionTypes\ShortAnswerHandler;
use App\QuestionTypes\TrueFalseHandler;
use InvalidArgumentException;

class QuestionTypeRegistry
{
    public function for(QuestionType $type): QuestionTypeHandler
    {
        return match ($type) {
            QuestionType::MULTIPLE_CHOICE => new MultipleChoiceHandler,
            QuestionType::MULTIPLE_SELECT => new MultipleSelectHandler,
            QuestionType::TRUE_FALSE => new TrueFalseHandler,
            QuestionType::SHORT_ANSWER => new ShortAnswerHandler,
            QuestionType::ESSAY => new EssayHandler,
            QuestionType::MATCHING => new MatchingHandler,
            QuestionType::ORDERING => new OrderingHandler,
            QuestionType::FILL_BLANK => new FillBlankHandler,
            default => throw new InvalidArgumentException("Unsupported question type: {$type->value}"),
        };
    }
}

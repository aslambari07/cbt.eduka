<?php

namespace App\Enums;

enum QuestionType: string
{
    case MULTIPLE_CHOICE = 'multiple_choice';
    case MULTIPLE_SELECT = 'multiple_select';
    case TRUE_FALSE = 'true_false';
    case SHORT_ANSWER = 'short_answer';
    case ESSAY = 'essay';
    case MATCHING = 'matching';
    case ORDERING = 'ordering';
    case FILL_BLANK = 'fill_blank';
}

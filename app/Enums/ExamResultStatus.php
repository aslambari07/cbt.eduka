<?php

namespace App\Enums;

enum ExamResultStatus: string
{
    case PENDING = 'pending';
    case GRADED = 'graded';
    case PUBLISHED = 'published';
    case VOID = 'void';
}

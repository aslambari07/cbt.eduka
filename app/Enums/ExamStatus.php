<?php

namespace App\Enums;

enum ExamStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case PUBLISHED = 'published';
    case ONGOING = 'ongoing';
    case COMPLETED = 'completed';
    case ARCHIVED = 'archived';
}

<?php

namespace App\Enums;

enum ExamSessionStatus: string
{
    case WAITING = 'waiting';
    case IN_PROGRESS = 'in_progress';
    case SAVING = 'saving';
    case SAVED = 'saved';
    case CONNECTION_LOST = 'connection_lost';
    case RECONNECTING = 'reconnecting';
    case TIME_WARNING = 'time_warning';
    case TIME_EXPIRED = 'time_expired';
    case SUBMITTED = 'submitted';
    case GRADING = 'grading';
    case COMPLETED = 'completed';
}

<?php

namespace App\Enums;

enum ExamAssignmentStatus: string
{
    case ASSIGNED = 'assigned';
    case STARTED = 'started';
    case SUBMITTED = 'submitted';
    case COMPLETED = 'completed';
    case EXPIRED = 'expired';
}

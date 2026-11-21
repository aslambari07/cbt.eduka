<?php

namespace App\Enums;

enum AiProviderStatus: string
{
    case NOT_CONFIGURED = 'NOT_CONFIGURED';
    case DISABLED = 'DISABLED';
    case READY = 'READY';
    case UNAVAILABLE = 'UNAVAILABLE';
    case RATE_LIMITED = 'RATE_LIMITED';
    case AUTHENTICATION_FAILED = 'AUTHENTICATION_FAILED';
    case PROVIDER_ERROR = 'PROVIDER_ERROR';
}

<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case COMPLETE = 'complete';
    case REFUNDED = 'refunded';
    case AMBIGUOUS = 'ambiguous';
    case NOT_FOUND = 'not_found';
    case CANCELLED = 'cancelled';
    case SERVICE_UNAVAILABLE = 'service_unavailable';
    case INVALID_RESPONSE = 'invalid_response';
    case UNKNOWN = 'unknown';
}

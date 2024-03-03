<?php

namespace App\Enums;

class PaymentStatus
{
    const PENDING = 'Pending';
    const PAID = 'Paid';
    const FAILED = 'Failed';
    const WAITING = 'Waiting';
    const INVALID = 'Invalid';

    /**
     * Get all enum values.
     *
     * @return array
     */
    public static function getValues(): array
    {
        return [
            self::PENDING,
            self::PAID,
            self::INVALID,
            self::FAILED,
        ];
    }
}

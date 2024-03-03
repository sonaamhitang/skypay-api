<?php

namespace App\Enums;

class ApiEnvironment
{
    const LIVE = 'LIVE';
    const UAT = 'UAT';

    /**
     * Get all enum values.
     *
     * @return array
     */
    public static function getValues(): array
    {
        return [
            self::UAT,
            self::LIVE,
        ];
    }
}

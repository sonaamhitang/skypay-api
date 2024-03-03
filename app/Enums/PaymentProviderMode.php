<?php

namespace App\Enums;

class PaymentProviderMode
{
    const MANUAL = 'Manual';
    const API = 'API';
    const ASSISTED = 'Assisted';

    /**
     * Get all enum values.
     *
     * @return array
     */
    public static function getValues(): array
    {
        return [
            self::MANUAL,
            self::API,
            self::ASSISTED,
        ];
    }
}

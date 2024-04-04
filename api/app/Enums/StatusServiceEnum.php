<?php

namespace App\Enums;

class StatusServiceEnum
{
    const PENDING = 'pending';
    const ACCEPTED = 'accepted';
    const ONGOING = 'ongoing';
    const COMPLETED = 'completed';
    const CANCELLED = 'cancelled';

    public static function toArray()
    {
        return [
            self::PENDING,
            self::ACCEPTED,
            self::ONGOING,
            self::COMPLETED,
            self::CANCELLED,
        ];
    }
}

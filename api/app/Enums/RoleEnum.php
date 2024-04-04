<?php

namespace App\Enums;

class RoleEnum
{
    const DRIVER = 'driver';
    const USER = 'user';

    public static function toArray()
    {
        return [
            self::DRIVER,
            self::USER,
        ];
    }
}

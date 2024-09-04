<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Enum;

enum DayTypeEnum: string
{
    case Monday = 'monday';
    case Tuesday = 'tuesday';
    case Wednesday = 'wednesday';
    case Thursday = 'thursday';
    case Friday = 'friday';
    case Saturday = 'saturday';
    case Sunday = 'sunday';

    public static function getDayByNumber(int $dayNumber): self
    {
        return match ($dayNumber) {
            1 => self::Monday,
            2 => self::Tuesday,
            3 => self::Wednesday,
            4 => self::Thursday,
            5 => self::Friday,
            6 => self::Saturday,
            7 => self::Sunday,
            default => throw new \InvalidArgumentException("Invalid day number: $dayNumber"),
        };
    }
}

<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Enum;

use MabeEnum\Enum;
use MabeEnum\EnumSerializableTrait;

class DayType extends Enum implements \Serializable
{
    use EnumSerializableTrait;

    public const MONDAY = 'monday';
    public const TUESDAY = 'tuesday';
    public const WEDNESDAY = 'wednesday';
    public const THURSDAY = 'thursday';
    public const FRIDAY = 'friday';
    public const SATURDAY = 'saturday';
    public const SUNDAY = 'sunday';

    public static function friday(): self
    {
        return static::get(static::FRIDAY);
    }

    public static function thursday(): self
    {
        return static::get(static::THURSDAY);
    }

    public static function wednesday(): self
    {
        return static::get(static::WEDNESDAY);
    }

    public static function tuesday(): self
    {
        return static::get(static::TUESDAY);
    }

    public static function monday(): self
    {
        return static::get(static::MONDAY);
    }

    public static function sunday(): self
    {
        return static::get(static::SUNDAY);
    }

    public static function saturday(): self
    {
        return static::get(static::SATURDAY);
    }
}

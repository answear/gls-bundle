<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Enum;

use MabeEnum\Enum;

class CountryCode extends Enum
{
    public const HUNGARY = 'HU';
    public const SLOVAKIA = 'SK';
    public const CZECH = 'CZ';
    public const ROMANIA = 'RO';
    public const SLOVENIA = 'SI';
    public const CROATIA = 'HR';

    public static function croatia(): self
    {
        return static::get(static::CROATIA);
    }

    public static function slovenia(): self
    {
        return static::get(static::SLOVENIA);
    }

    public static function romania(): self
    {
        return static::get(static::ROMANIA);
    }

    public static function czech(): self
    {
        return static::get(static::CZECH);
    }

    public static function slovakia(): self
    {
        return static::get(static::SLOVAKIA);
    }

    public static function hungary(): self
    {
        return static::get(static::HUNGARY);
    }
}

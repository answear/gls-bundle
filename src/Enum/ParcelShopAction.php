<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Enum;

use MabeEnum\Enum;

class ParcelShopAction extends Enum
{
    public const GET_LIST = 'getList';
    public const GET_LNG2 = 'getLng2';
    public const GET_OPENINGS = 'getOpenings';

    public static function getOpenings(): self
    {
        return static::get(static::GET_OPENINGS);
    }

    public static function getLng2(): self
    {
        return static::get(static::GET_LNG2);
    }

    public static function getList(): self
    {
        return static::get(static::GET_LIST);
    }
}

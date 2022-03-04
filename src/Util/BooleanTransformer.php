<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Util;

class BooleanTransformer
{
    private const TRUE_VALUES = ['t', 1, '1', 'true', true, 'yes'];

    private const FALSE_VALUES = ['f', 0, '0', 'false', false, 'no'];

    public static function transformToBoolean($value): bool
    {
        if (\in_array($value, self::TRUE_VALUES, true)) {
            return true;
        }

        if (\in_array($value, self::FALSE_VALUES, true)) {
            return false;
        }

        throw new \InvalidArgumentException('Invalid value.');
    }

    public static function getSupportedValues(): array
    {
        return array_merge(self::TRUE_VALUES, self::FALSE_VALUES);
    }
}

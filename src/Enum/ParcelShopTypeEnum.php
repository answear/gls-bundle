<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Enum;

enum ParcelShopTypeEnum: string
{
    case ParcelLocker = 'parcel-locker';
    case ParcelShop = 'parcel-shop';
}

<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Enum;

enum MyGlsDeliveryPointTypeEnum: int
{
    case ParcelShop = 1;
    case ParcelLocker = 2;
    case Depot = 3;
}

<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Enum;

enum FeatureEnum: string implements \JsonSerializable
{
    case AcceptsCash = 'acceptsCash';
    case AcceptsCard = 'acceptsCard';
    case Pickup = 'pickup';
    case Delivery = 'delivery';

    public static function isAcceptsCard(string $value): bool
    {
        return $value === self::AcceptsCard->value;
    }

    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
        ];
    }
}

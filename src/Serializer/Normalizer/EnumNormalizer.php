<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Serializer\Normalizer;

use MabeEnum\Enum;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EnumNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function getSupportedTypes(?string $format): array
    {
        return [Enum::class => true];
    }

    /**
     * @return array|string|int|float|bool|\ArrayObject|null
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof Enum) {
            throw new InvalidArgumentException(sprintf('The object must implement "%s".', Enum::class));
        }

        return $object->getValue();
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof Enum;
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        try {
            $type::get($data);
        } catch (\Throwable $t) {
            return false;
        }

        return true;
    }

    /**
     * @return array|string|int|float|bool|\ArrayObject|null
     *
     * @see Enum for $type
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        return $type::get($data);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return __CLASS__ === static::class;
    }
}

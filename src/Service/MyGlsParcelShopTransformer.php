<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Service;

use Answear\GlsBundle\Enum\CountryCodeEnum;
use Answear\GlsBundle\Enum\FeatureEnum;
use Answear\GlsBundle\Enum\MyGlsDeliveryPointTypeEnum;
use Answear\GlsBundle\Response\DTO\Address;
use Answear\GlsBundle\Response\DTO\Coordinates;
use Answear\GlsBundle\Response\DTO\Openings;
use Answear\GlsBundle\Response\DTO\ParcelShop;

class MyGlsParcelShopTransformer
{
    public function transform(array $point, CountryCodeEnum $countryCode): ?ParcelShop
    {
        if (false === ($point['IsActive'] ?? true)) {
            return null;
        }

        $id = (string) ($point['Id'] ?? '');
        if ('' === $id) {
            return null;
        }

        $addressData = $point['Address'] ?? [];
        if (!is_array($addressData)) {
            return null;
        }

        $address = $this->createAddress($addressData, $countryCode);
        if (null === $address) {
            return null;
        }

        $openingList = $point['DeliveryPointOpeningList'] ?? [];
        $openings = is_array($openingList) ? $this->createOpenings($openingList) : [];

        $cardPaymentAllowed = (bool) ($point['CardPaymentAllowed'] ?? false);
        $isCodHandler = (bool) ($point['CodHandler'] ?? false);

        $type = MyGlsDeliveryPointTypeEnum::tryFrom((int) ($point['DeliveryPointType'] ?? 0));
        $isParcelLocker = MyGlsDeliveryPointTypeEnum::ParcelLocker === $type;

        $features = [];
        if ($cardPaymentAllowed) {
            $features[] = FeatureEnum::AcceptsCard;
        }
        if ($isCodHandler) {
            $features[] = FeatureEnum::AcceptsCash;
        }

        $pickupTime = isset($point['PickupTime']) && '' !== (string) $point['PickupTime']
            ? (string) $point['PickupTime']
            : null;

        return new ParcelShop(
            $id,
            (string) ($addressData['Name'] ?? ''),
            $countryCode,
            $isCodHandler,
            $cardPaymentAllowed,
            $isParcelLocker,
            pickupTime: $pickupTime,
            info: null,
            address: $address,
            openings: $openings,
            coordinates: new Coordinates(
                (float) ($point['Latitude'] ?? 0.0),
                (float) ($point['Longitude'] ?? 0.0)
            ),
            features: $features
        );
    }

    private function createAddress(array $addressData, CountryCodeEnum $countryCode): ?Address
    {
        $city = (string) ($addressData['City'] ?? '');
        if ('' === $city) {
            return null;
        }

        $street = trim(sprintf('%s %s', $addressData['Street'] ?? '', $addressData['HouseNumber'] ?? ''));

        return new Address(
            $countryCode->value,
            (string) ($addressData['ZipCode'] ?? ''),
            $city,
            $street,
            (string) ($addressData['Name'] ?? ''),
            (string) ($addressData['ContactPhone'] ?? ''),
            null,
            null
        );
    }

    /**
     * @return Openings[]
     */
    private function createOpenings(array $openingList): array
    {
        $openings = [];
        foreach ($openingList as $opening) {
            if (!is_array($opening)) {
                continue;
            }

            $dayOfWeek = (int) ($opening['DayOfWeek'] ?? 0);
            if ($dayOfWeek < 1 || $dayOfWeek > 7) {
                continue;
            }

            $open = $opening['Open'] ?? '';
            if (!is_string($open) || '' === $open) {
                continue;
            }

            $openParts = explode('-', $open);
            $openTime = $openParts[0];
            $closeTime = $openParts[1] ?? '';

            $break = $opening['Break'] ?? null;
            $breakStart = null;
            $breakEnd = null;
            if (is_string($break) && '' !== $break && str_contains($break, '-')) {
                $breakParts = explode('-', $break);
                $breakStart = $breakParts[0];
                $breakEnd = $breakParts[1] ?? null;
            }

            $openings[] = new Openings(
                $dayOfWeek,
                $openTime,
                $closeTime,
                $breakStart,
                $breakEnd
            );
        }

        return $openings;
    }
}

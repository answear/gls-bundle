<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Service;

use Answear\GlsBundle\Client\Client;
use Answear\GlsBundle\ConfigProvider;
use Answear\GlsBundle\Enum\FeatureEnum;
use Answear\GlsBundle\Exception\ServiceUnavailableException;
use Answear\GlsBundle\Request\GetDeliveryPoints;
use Answear\GlsBundle\Request\GetParcelShops;
use Answear\GlsBundle\Response\DTO\Address;
use Answear\GlsBundle\Response\DTO\Coordinates;
use Answear\GlsBundle\Response\DTO\MyGlsResponse;
use Answear\GlsBundle\Response\DTO\Openings;
use Answear\GlsBundle\Response\DTO\ParcelShop;
use Answear\GlsBundle\Response\DTO\RawResponse;
use Answear\GlsBundle\Serializer\Serializer;

class ParcelShopsService
{
    private Client $client;
    private Serializer $serializer;
    private ConfigProvider $configProvider;

    public function __construct(Client $client, Serializer $serializer, ConfigProvider $configProvider)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->configProvider = $configProvider;
    }

    /**
     * @return ParcelShop[]
     *
     * @throws ServiceUnavailableException|\Throwable
     */
    public function getParcelShopCollection(): array
    {
        if ($this->configProvider->isMyGlsEnabled()) {
            return $this->getMyGlsParcelShopCollection();
        }

        $request = new GetParcelShops($this->configProvider->getCountryCode());

        $response = $this->client->request($request);

        /** @var RawResponse $rawResponse */
        $rawResponse = $this->serializer->decodeResponse(sprintf('%s', RawResponse::class), $response);

        $parcelShopCollection = [];
        foreach ($rawResponse->items as $rawParcelShop) {
            $parcelShopCollection[] = ParcelShop::fromRawParcelShop($rawParcelShop);
        }

        return $parcelShopCollection;
    }

    /**
     * @return ParcelShop[]
     *
     * @throws ServiceUnavailableException|\Throwable
     */
    private function getMyGlsParcelShopCollection(): array
    {
        $passwordPlain = $this->configProvider->getPassword() ?? '';
        $hash = \hash('sha512', $passwordPlain, true);
        $unpack = \unpack('C*', $hash);
        if (false === $unpack) {
            throw new \RuntimeException('Failed to unpack hashed password.');
        }
        $passwordHash = \array_values($unpack);

        $request = new GetDeliveryPoints(
            $this->configProvider->getUsername() ?? '',
            $passwordHash,
            $this->configProvider->getCountryCode()->value
        );

        $response = $this->client->request($request);

        /** @var MyGlsResponse $myGlsResponse */
        $myGlsResponse = $this->serializer->decodeResponse(MyGlsResponse::class, $response);

        if (0 !== $myGlsResponse->ErrorCode) {
            throw new \RuntimeException(
                sprintf(
                    'MyGLS API error: %s (code: %s)',
                    $myGlsResponse->ErrorDescription ?? 'Unknown error',
                    $myGlsResponse->ErrorCode
                )
            );
        }

        if (empty($myGlsResponse->Data)) {
            return [];
        }

        $binary = \pack('C*', ...$myGlsResponse->Data);
        $decryptedAndDecompressed = \gzdecode($binary);
        if (false === $decryptedAndDecompressed) {
            throw new \RuntimeException('Failed to decompress Slovakian GLS delivery points data.');
        }

        /** @var array<string, mixed>[] $allPoints */
        $allPoints = \json_decode($decryptedAndDecompressed, true, flags: JSON_THROW_ON_ERROR);

        $parcelShopCollection = [];
        foreach ($allPoints as $point) {
            $pointType = $point['DeliveryPointType'] ?? 0;
            if (1 === $pointType || 2 === $pointType) {
                $parcelShopCollection[] = $this->mapMyGlsPoint($point);
            }
        }

        return $parcelShopCollection;
    }

    private function mapMyGlsPoint(array $point): ParcelShop
    {
        $addressData = $point['Address'] ?? [];
        $street = trim(sprintf('%s %s', $addressData['Street'] ?? '', $addressData['HouseNumber'] ?? ''));

        $address = new Address(
            $this->configProvider->getCountryCode()->value,
            (string) ($addressData['ZipCode'] ?? ''),
            (string) ($addressData['City'] ?? ''),
            $street,
            (string) ($addressData['Name'] ?? ''),
            (string) ($addressData['ContactPhone'] ?? ''),
            null,
            null
        );

        $openings = [];
        foreach ($point['DeliveryPointOpeningList'] ?? [] as $opening) {
            $dayOfWeek = (int) ($opening['DayOfWeek'] ?? 0);
            if ($dayOfWeek < 1 || $dayOfWeek > 7) {
                continue;
            }

            $open = $opening['Open'] ?? '';
            $openParts = explode('-', $open);
            $openTime = $openParts[0];
            $closeTime = $openParts[1] ?? '';

            $break = $opening['Break'] ?? '';
            $breakStart = null;
            $breakEnd = null;
            if (!empty($break) && str_contains($break, '-')) {
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

        $features = [];
        $cardPaymentAllowed = (bool) ($point['CardPaymentAllowed'] ?? false);
        if ($cardPaymentAllowed) {
            $features[] = FeatureEnum::AcceptsCard;
        }
        $features[] = FeatureEnum::AcceptsCash;

        $isParcelLocker = 2 === ($point['DeliveryPointType'] ?? 0);

        return new ParcelShop(
            (string) ($point['Id'] ?? ''),
            (string) ($addressData['Name'] ?? ''),
            $this->configProvider->getCountryCode(),
            true,
            $cardPaymentAllowed,
            $isParcelLocker,
            pickupTime: null,
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
}

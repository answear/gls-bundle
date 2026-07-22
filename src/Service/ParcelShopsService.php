<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Service;

use Answear\GlsBundle\Client\Client;
use Answear\GlsBundle\ConfigProvider;
use Answear\GlsBundle\Exception\ServiceUnavailableException;
use Answear\GlsBundle\Request\GetDeliveryPoints;
use Answear\GlsBundle\Request\GetParcelShops;
use Answear\GlsBundle\Response\DTO\MyGlsResponse;
use Answear\GlsBundle\Response\DTO\ParcelShop;
use Answear\GlsBundle\Response\DTO\RawResponse;
use Answear\GlsBundle\Serializer\Serializer;

class ParcelShopsService
{
    private const PACK_CHUNK_SIZE = 5000;

    private Client $client;
    private Serializer $serializer;
    private ConfigProvider $configProvider;
    private MyGlsParcelShopTransformer $transformer;

    public function __construct(
        Client $client,
        Serializer $serializer,
        ConfigProvider $configProvider,
        ?MyGlsParcelShopTransformer $transformer = null,
    ) {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->configProvider = $configProvider;
        $this->transformer = $transformer ?? new MyGlsParcelShopTransformer();
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
        $username = $this->configProvider->getUsername();
        $passwordPlain = $this->configProvider->getPassword();

        if (empty($username) || empty($passwordPlain)) {
            throw new \InvalidArgumentException('MyGLS credentials (username and password) must be configured.');
        }

        $hash = \hash('sha512', $passwordPlain, true);
        $unpack = \unpack('C*', $hash);
        if (false === $unpack) {
            throw new \RuntimeException('Failed to unpack hashed password.');
        }
        $passwordHash = \array_values($unpack);

        $request = new GetDeliveryPoints(
            $username,
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

        $binary = '';
        foreach (array_chunk($myGlsResponse->Data, self::PACK_CHUNK_SIZE) as $chunk) {
            $binary .= \pack('C*', ...$chunk);
        }
        $decompressed = \gzdecode($binary);
        if (false === $decompressed) {
            throw new \RuntimeException(
                sprintf(
                    'Failed to decompress GLS %s delivery points data.',
                    $this->configProvider->getCountryCode()->value
                )
            );
        }

        /** @var mixed[] $allPoints */
        $allPoints = \json_decode($decompressed, true, flags: JSON_THROW_ON_ERROR);

        $parcelShopCollection = [];
        foreach ($allPoints as $point) {
            if (!is_array($point)) {
                continue;
            }

            $parcelShop = $this->transformer->transform($point, $this->configProvider->getCountryCode());
            if (null !== $parcelShop) {
                $parcelShopCollection[] = $parcelShop;
            }
        }

        return $parcelShopCollection;
    }
}

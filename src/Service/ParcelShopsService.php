<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Service;

use Answear\GlsBundle\Client\Client;
use Answear\GlsBundle\ConfigProvider;
use Answear\GlsBundle\Exception\ServiceUnavailableException;
use Answear\GlsBundle\Request\GetParcelShops;
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
}

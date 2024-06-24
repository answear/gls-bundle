<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Service;

use Answear\GlsBundle\Client\Client;
use Answear\GlsBundle\ConfigProvider;
use Answear\GlsBundle\Enum\ParcelShopAction;
use Answear\GlsBundle\Exception\ServiceUnavailableException;
use Answear\GlsBundle\Request\GetParcelShops;
use Answear\GlsBundle\Response\DTO\Openings;
use Answear\GlsBundle\Response\DTO\ParcelShop;
use Answear\GlsBundle\Response\DTO\RawParcelShop;
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
     * @throws ServiceUnavailableException
     */
    public function getParcelShopCollection(
        ?bool $withPclShopIn = true,
        ?bool $withParcelLockIn = true,
        ?string $senderId = null,
        ?bool $codHandler = null,
        ?bool $dropOff = null
    ): array {
        $rawParcelShopCollection = $this->getList(
            $withPclShopIn,
            $withParcelLockIn,
            $senderId,
            $codHandler,
            $dropOff
        );

        $parcelShopCollection = [];
        foreach ($rawParcelShopCollection as $rawParcelShop) {
            try {
                $openings = $this->getOpenings($rawParcelShop->pclshopid);
            } catch (\Throwable $throwable) {
                $openings = [];
            }

            $parcelShop = ParcelShop::fromRawParcelShop($rawParcelShop, $openings);
            $parcelShopCollection[] = $parcelShop;
        }

        return $parcelShopCollection;
    }

    /**
     * @return RawParcelShop[]
     *
     * @throws ServiceUnavailableException
     */
    public function getList(
        ?bool $withPclShopIn = null,
        ?bool $withParcelLockIn = null,
        ?string $senderId = null,
        ?bool $codHandler = null,
        ?bool $dropOff = null
    ): array {
        $request = new GetParcelShops(
            ParcelShopAction::getList(),
            $this->configProvider->getCountryCode(),
            $senderId,
            $withPclShopIn,
            $withParcelLockIn,
            $codHandler,
            $dropOff
        );

        $response = $this->client->request($request);

        return $this->serializer->decodeResponse(sprintf('%s[]', RawParcelShop::class), $response);
    }

    /**
     * @return Openings[]
     */
    public function getOpenings(string $shopId): array
    {
        $request = new GetParcelShops(
            ParcelShopAction::getOpenings(),
            null,
            null,
            null,
            null,
            null,
            null,
            $shopId
        );
        $response = $this->client->request($request);

        return $this->serializer->decodeResponse(sprintf('%s[]', Openings::class), $response);
    }
}

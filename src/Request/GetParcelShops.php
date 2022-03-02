<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Request;

use Answear\GlsBundle\Enum\CountryCode;
use Answear\GlsBundle\Enum\ParcelShopAction;

class GetParcelShops implements RequestInterface
{
    private const ENDPOINT = 'psmap/psmap_getdata.php';
    private const HTTP_METHOD = 'GET';

    private ParcelShopAction $action;
    private ?CountryCode $countryCode;
    private ?string $senderId;
    private ?bool $pclShopIn;
    private ?bool $parcelLockIn;
    private ?bool $codHandler;
    private ?bool $dropOff;
    private ?string $shopId;

    public function __construct(
        ParcelShopAction $action,
        ?CountryCode $countryCode = null,
        ?string $senderId = null,
        ?bool $withPclShopIn = null,
        ?bool $withParcelLockIn = null,
        ?bool $codHandler = null,
        ?bool $dropOff = null,
        ?string $shopId = null
    ) {
        $this->action = $action;
        $this->countryCode = $countryCode;
        $this->senderId = $senderId;
        $this->pclShopIn = $withPclShopIn;
        $this->parcelLockIn = $withParcelLockIn;
        $this->codHandler = $codHandler;
        $this->dropOff = $dropOff;
        $this->shopId = $shopId;
    }

    public function getEndpoint(): string
    {
        return self::ENDPOINT;
    }

    public function getMethod(): string
    {
        return self::HTTP_METHOD;
    }

    public function getUrlQuery(): ?string
    {
        $urlQuery = http_build_query(
            [
                'action' => $this->action->getValue(),
                'ctrcode' => null === $this->countryCode ? null : $this->countryCode->getValue(),
                'senderid' => $this->senderId,
                'pclshopin' => null === $this->pclShopIn ? null : (int) $this->pclShopIn,
                'parcellockin' => null === $this->parcelLockIn ? null : (int) $this->parcelLockIn,
                'codhandler' => null === $this->codHandler ? null : (int) $this->codHandler,
                'dropoff' => null === $this->dropOff ? null : (int) $this->dropOff,
                'pclshopid' => $this->shopId,
            ]
        );

        return empty($urlQuery) ? null : $urlQuery;
    }
}

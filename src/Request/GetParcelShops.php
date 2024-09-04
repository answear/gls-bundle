<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Request;

use Answear\GlsBundle\Enum\CountryCodeEnum;

class GetParcelShops implements RequestInterface
{
    private const ENDPOINT = 'data/deliveryPoints/';
    private const HTTP_METHOD = 'GET';

    public function __construct(public CountryCodeEnum $countryCode)
    {
    }

    public function getEndpoint(): string
    {
        return self::ENDPOINT . $this->getJsonFile();
    }

    public function getMethod(): string
    {
        return self::HTTP_METHOD;
    }

    private function getJsonFile(): string
    {
        return match ($this->countryCode) {
            CountryCodeEnum::Hungary => 'hu.json',
            CountryCodeEnum::Slovakia => 'sk.json',
            CountryCodeEnum::Czech => 'cz.json',
            CountryCodeEnum::Romania => 'ro.json',
            CountryCodeEnum::Slovenia => 'si.json',
            CountryCodeEnum::Croatia => 'hr.json',
        };
    }
}

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
        return sprintf('%s.json', strtolower($this->countryCode->value));
    }
}

<?php

declare(strict_types=1);

namespace Answear\GlsBundle;

use Answear\GlsBundle\Enum\CountryCode;

class ConfigProvider
{
    private const URL_MAP = [
        CountryCode::HUNGARY => 'https://online.gls-hungary.com/',
        CountryCode::SLOVAKIA => 'https://online.gls-slovakia.com/',
        CountryCode::CZECH => 'https://online.gls-czech.com/',
        CountryCode::ROMANIA => 'https://online.gls-romania.com/',
        CountryCode::SLOVENIA => 'https://online.gls-slovenia.com/',
        CountryCode::CROATIA => 'https://online.gls-croatia.com/',
    ];

    private CountryCode $countryCode;

    public function __construct(string $countryCode)
    {
        $this->countryCode = CountryCode::byValue($countryCode);
    }

    public function getCountryCode(): CountryCode
    {
        return $this->countryCode;
    }

    public function getUrl(): string
    {
        $url = self::URL_MAP[$this->getCountryCode()->getValue()] ?? null;
        if (null === $url) {
            throw new \InvalidArgumentException('No url for provided country code.');
        }

        return $url;
    }
}

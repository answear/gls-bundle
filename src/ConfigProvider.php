<?php

declare(strict_types=1);

namespace Answear\GlsBundle;

use Answear\GlsBundle\Enum\CountryCodeEnum;

class ConfigProvider
{
    private CountryCodeEnum $countryCode;

    public function __construct(string $countryCode)
    {
        $this->countryCode = CountryCodeEnum::from($countryCode);
    }

    public function getCountryCode(): CountryCodeEnum
    {
        return $this->countryCode;
    }

    public function getUrl(): string
    {
        return match ($this->getCountryCode()) {
            CountryCodeEnum::Romania,
            CountryCodeEnum::Hungary => 'https://map.gls-hungary.com/',
            CountryCodeEnum::Slovakia => 'https://map.gls-slovakia.com/',
            CountryCodeEnum::Czech => 'https://map.gls-czech.com/',
            CountryCodeEnum::Slovenia => 'https://map.gls-slovenia.com/',
            CountryCodeEnum::Croatia => 'https://map.gls-croatia.com/',
        };
    }
}

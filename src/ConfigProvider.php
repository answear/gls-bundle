<?php

declare(strict_types=1);

namespace Answear\GlsBundle;

use Answear\GlsBundle\Enum\CountryCodeEnum;

class ConfigProvider
{
    public const URL_MAP = [
        CountryCodeEnum::Romania->value => 'https://map.gls-hungary.com/',
        CountryCodeEnum::Hungary->value => 'https://map.gls-hungary.com/',
        CountryCodeEnum::Slovakia->value => 'https://map.gls-slovakia.com/',
        CountryCodeEnum::Czech->value => 'https://map.gls-czech.com/',
        CountryCodeEnum::Slovenia->value => 'https://map.gls-slovenia.com/',
        CountryCodeEnum::Croatia->value => 'https://map.gls-croatia.com/',
    ];

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
        return self::URL_MAP[$this->countryCode->value];
    }
}

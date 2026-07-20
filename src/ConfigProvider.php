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

    public const MYGLS_URL_MAP = [
        CountryCodeEnum::Romania->value => 'https://api.mygls.ro/',
        CountryCodeEnum::Hungary->value => 'https://api.mygls.hu/',
        CountryCodeEnum::Slovakia->value => 'https://api.mygls.sk/',
        CountryCodeEnum::Czech->value => 'https://api.mygls.cz/',
        CountryCodeEnum::Slovenia->value => 'https://api.mygls.si/',
        CountryCodeEnum::Croatia->value => 'https://api.mygls.hr/',
    ];

    private CountryCodeEnum $countryCode;
    private ?string $username;
    private ?string $password;
    private ?string $apiUrl;

    public function __construct(
        string $countryCode,
        ?string $username = null,
        ?string $password = null,
        ?string $apiUrl = null,
    ) {
        $this->countryCode = CountryCodeEnum::from($countryCode);
        $this->username = $username;
        $this->password = $password;
        $this->apiUrl = $apiUrl;
    }

    public function getCountryCode(): CountryCodeEnum
    {
        return $this->countryCode;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getApiUrl(): ?string
    {
        return $this->apiUrl;
    }

    public function isMyGlsEnabled(): bool
    {
        return !empty($this->username) && !empty($this->password);
    }

    public function getUrl(): string
    {
        if ($this->isMyGlsEnabled()) {
            return $this->apiUrl ?? self::MYGLS_URL_MAP[$this->countryCode->value];
        }

        return self::URL_MAP[$this->countryCode->value];
    }
}

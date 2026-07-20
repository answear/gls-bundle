<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Request;

readonly class GetDeliveryPoints implements RequestInterface
{
    private const ENDPOINT = 'MasterDataService.svc/json/GetDeliveryPoints';
    private const HTTP_METHOD = 'POST';

    /**
     * @param int[] $Password
     */
    public function __construct(
        public string $Username,
        public array $Password,
        public string $CountryIsoCode,
    ) {
    }

    public function getEndpoint(): string
    {
        return self::ENDPOINT;
    }

    public function getMethod(): string
    {
        return self::HTTP_METHOD;
    }
}

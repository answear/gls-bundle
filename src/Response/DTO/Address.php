<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Response\DTO;

readonly class Address
{
    public function __construct(
        public string $countryCode,
        public string $zipCode,
        public string $city,
        public string $place,
        public ?string $name,
        public ?string $phone,
        public ?string $email,
        public ?string $web,
    ) {
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            $response['countryCode'],
            $response['postalCode'],
            $response['city'],
            $response['address'],
            $response['name'] ?? null,
            $response['phone'] ?? null,
            $response['email'] ?? null,
            $response['web'] ?? null,
        );
    }
}

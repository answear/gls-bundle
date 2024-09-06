<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Response\DTO;

class RawParcelShop
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description,
        public array $contact,
        public array $location,
        public array $hours,
        public array $features,
        public string $type,
        public ?string $pickupTime = null,
    ) {
    }
}

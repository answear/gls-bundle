<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Response\DTO;

class RawResponse
{
    /**
     * @var RawParcelShop[]
     */
    public array $items = [];

    public function addItem(RawParcelShop $item): void
    {
        $this->items[] = $item;
    }
}

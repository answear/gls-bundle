<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Response\DTO;

class MyGlsResponse
{
    public int $ErrorCode = 0;
    public ?string $ErrorDescription = null;
    /**
     * @var int[]
     */
    public array $Data = [];
}

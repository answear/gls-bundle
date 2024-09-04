<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Response\DTO;

use Answear\GlsBundle\Enum\DayTypeEnum;

class Openings
{
    public DayTypeEnum $day;

    public function __construct(
        public int $number,
        public string $openTime,
        public string $closeTime,
        public ?string $breakStart = null,
        public ?string $breakEnd = null,
    ) {
        $this->day = DayTypeEnum::getDayByNumber($number);
    }

    public static function fromResponse(array $responseHours): self
    {
        return new self(
            (int) $responseHours[0],
            $responseHours[1],
            $responseHours[2],
            $responseHours[3] ?? null,
            $responseHours[4] ?? null
        );
    }
}

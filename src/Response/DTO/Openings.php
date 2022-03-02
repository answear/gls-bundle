<?php

declare(strict_types=1);

namespace Answear\GlsBundle\Response\DTO;

use Answear\GlsBundle\Enum\DayType;

class Openings
{
    private DayType $day;
    private string $open;
    private string $midbreak;

    public function __construct(
        DayType $day,
        string $open,
        string $midbreak
    ) {
        $this->day = $day;
        $this->open = $open;
        $this->midbreak = $midbreak;
    }

    public function getDay(): DayType
    {
        return $this->day;
    }

    public function getOpen(): string
    {
        return $this->open;
    }

    public function getMidbreak(): string
    {
        return $this->midbreak;
    }
}

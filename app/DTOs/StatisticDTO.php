<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class StatisticDTO
{
    public function __construct(public string $startPrice, public string $endPrice, public string $percentage) {}

    public function getPercentage(): string
    {
        return $this->percentage;
    }

    public function getEndPrice(): string
    {
        return $this->endPrice;
    }

    public function getStartPrice(): string
    {
        return $this->startPrice;
    }
}

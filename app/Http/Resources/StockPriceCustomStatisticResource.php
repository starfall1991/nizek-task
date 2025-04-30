<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\StatisticDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StockPriceCustomStatisticResource extends JsonResource
{
    /**
     * @return array<string, string>
     */
    public function toArray(Request $request): array
    {
        /** @var StatisticDTO $statisticDto */
        $statisticDto = $this->resource;

        return [
            'start_price' => $statisticDto->getStartPrice(),
            'end_price' => $statisticDto->getEndPrice(),
            'percentage' => $statisticDto->getPercentage() . "%",
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\StockPriceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class StoreStockPriceFileJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $path) {}

    public function handle(): void
    {
        app(StockPriceService::class)->storeByFilePath($this->path);
    }
}

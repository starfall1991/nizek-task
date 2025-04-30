<?php

declare(strict_types=1);

use App\Http\Controllers\StockPriceController;
use Illuminate\Support\Facades\Route;

Route::prefix('stock-prices')->group(static function (): void {
    Route::get('statistics', [StockPriceController::class, 'statistics']);
    Route::get('custom-statistics', [StockPriceController::class, 'customStatistics']);
    Route::post('upload', [StockPriceController::class, 'upload']);
});

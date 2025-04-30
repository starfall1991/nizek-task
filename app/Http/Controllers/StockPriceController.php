<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StockPriceCustomStatisticRequest;
use App\Http\Requests\StockPriceUploadRequest;
use App\Http\Resources\StockPriceCustomStatisticResource;
use App\Http\Resources\StockPriceStatisticsResource;
use App\Jobs\StoreStockPriceFileJob;
use App\Services\StockPriceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

final class StockPriceController extends Controller
{
    public function statistics(): JsonResource
    {
        $result = app(StockPriceService::class)->getStatistics();
        return StockPriceStatisticsResource::make($result);
    }

    public function customStatistics(StockPriceCustomStatisticRequest $request): JsonResource
    {
        $start = Carbon::parse($request->input('start'));
        $end = Carbon::parse($request->input('end'));
        $result = app(StockPriceService::class)->getCustomStatistic($start, $end);
        return StockPriceCustomStatisticResource::make($result);
    }

    public function upload(StockPriceUploadRequest $request): JsonResponse
    {
        $result = Storage::disk('local')->putFile("", $request->file('file'));
        if (false === $result) {
            return response()->json(['error' => 'Upload failed'], 500);
        }

        dispatch(new StoreStockPriceFileJob($result))->onQueue("store_stock_prices");
        return response()->json(['data' => null]);
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\StatisticDTO;
use App\Enums\CacheKeyEnum;
use App\Models\StockPrice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

final class StockPriceService
{
    /**
     * @return array<string, int>
     */
    public function getStatistics(): array
    {
        $result = Cache::remember(CacheKeyEnum::STOCK_PRICE_STATISTICS->value, now()->endOfDay(), static function () {
            $recentRecord = StockPrice::query()
                ->orderBy('date', 'desc')
                ->first(['date', 'price']);

            if (!$recentRecord) {
                return [];
            }

            $oldestRecord = StockPrice::query()
                ->orderBy('date', 'asc')
                ->firstOrFail(['date', 'price']);

            $recentDate = Carbon::parse($recentRecord->date);
            $oldDates = [
                $recentDate->copy()->subDays()->format('Y-m-d H:i:s') => '1D',
                $recentDate->copy()->subMonth()->format('Y-m-d H:i:s') => '1M',
                $recentDate->copy()->subMonths(3)->format('Y-m-d H:i:s') => '3M',
                $recentDate->copy()->subMonths(6)->format('Y-m-d H:i:s') => '6M',
                $recentDate->copy()->firstOfYear()->format('Y-m-d H:i:s') => 'YTD',
                $recentDate->copy()->subYear()->format('Y-m-d H:i:s') => '1Y',
                $recentDate->copy()->subYears(3)->format('Y-m-d H:i:s') => '3Y',
                $recentDate->copy()->subYears(5)->format('Y-m-d H:i:s') => '5Y',
                $recentDate->copy()->subYears(10)->format('Y-m-d H:i:s') => '10Y',
            ];

            $stockPrices = StockPrice::query()
                ->dateIn(array_keys($oldDates))
                ->orderBy('date', 'desc')
                ->get(['date', 'price']);

            $result = [];
            foreach ($stockPrices as $stockPrice) {
                $duration = $oldDates[$stockPrice->date];
                $percentage = (($recentRecord->price / $stockPrice->price) - 1) * 100;
                $result[$duration] = (string) $percentage;
            }
            $result['MAX'] = (string) ((($recentRecord->price / $oldestRecord->price) - 1) * 100);
            return $result;
        });

        // if the result is not complete try to get them again
        if (count($result) != 10) {
            Cache::forget(CacheKeyEnum::STOCK_PRICE_STATISTICS->value);
        }

        return $result;
    }

    public function getCustomStatistic(Carbon $start, Carbon $end): ?StatisticDTO
    {
        $stockPrices = StockPrice::query()
            ->dateIn([$start, $end])
            ->orderBy('date', 'asc')
            ->get(['date', 'price']);

        if (2 !== $stockPrices->count()) {
            return null;
        }

        $oldRecord = $stockPrices->first();
        $recentRecord = $stockPrices->last();

        $percentage = (string) ((($recentRecord->price / $oldRecord->price) - 1) * 100);
        return new StatisticDTO((string) $oldRecord->price, (string) $recentRecord->price, $percentage);
    }

    public function storeByFilePath(string $path): void
    {
        $convertedPath = $this->convertToCsv($path);

        if (($handle = fopen($convertedPath, 'r')) !== false) {
            $data = [];
            $i = 0;
            // 100 is more than enough for 2025/04/15,157.869995 for two columns
            while (($row = fgetcsv($handle, 100)) !== false) {
                if (isset($row[0], $row[1])) {
                    $tmp = [];
                    try {
                        $tmp['date'] = Carbon::parse($row[0]);
                    } catch (Throwable $throwable) {
                        continue;
                    }
                    $tmp['price'] = $row[1];
                    $data[] = $tmp;
                    $i++;
                }

                $chunkSize = config("services.stock_price.chunk_size");
                if ($i > $chunkSize) {
                    StockPrice::insertOrIgnore($data);
                    $data = [];
                }
            }
        }

        Storage::disk('local')->delete($path);
        File::delete($convertedPath);
    }

    public function convertToCsv(string $path): string
    {
        // create empty csv file
        Storage::disk('local')->put($this->csvMaker($path), "");

        $notConvertedPath = Storage::disk('local')->path($path);
        $convertedPath = $this->csvMaker($notConvertedPath);

        // make sure gnumeric is installed
        $execCommand = "ssconvert \"{$notConvertedPath}\" \"{$convertedPath}\"";
        exec($execCommand);

        return $convertedPath;
    }

    public function csvMaker(string $path): string
    {
        return Str::of($path)->beforeLast('.')->value() . "-converted.csv";
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\StockPriceFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class StockPrice extends Model
{
    /** @use HasFactory<StockPriceFactory> */
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        "price",
        "date",
    ];

    #[Scope]
    public function dateIn(Builder $builder, array $dates): void
    {
        $builder->whereIn('date', $dates);
    }
}

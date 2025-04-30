<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\StockPrice;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StockPriceCustomStatisticRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start' => [
                'required',
                'date_format:Y-m-d H:i:s',
                Rule::exists(StockPrice::class, 'date'),
            ],
            'end' => [
                'required',
                'date_format:Y-m-d H:i:s',
                'after:start',
                Rule::exists(StockPrice::class, 'date'),
            ],
        ];
    }
}

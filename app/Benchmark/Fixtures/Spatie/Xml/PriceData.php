<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Spatie\Xml;

use Spatie\LaravelData\Data;

final class PriceData extends Data
{
    public function __construct(
        public readonly float $amount,
        public readonly string $currency,
    ) {}
}

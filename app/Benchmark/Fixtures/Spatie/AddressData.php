<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Spatie;

use Spatie\LaravelData\Data;

final class AddressData extends Data
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly ?string $zip = null,
    ) {}
}

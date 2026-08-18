<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Spatie;

use Spatie\LaravelData\Data;

final class PersonData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly AddressData $address,
    ) {}
}

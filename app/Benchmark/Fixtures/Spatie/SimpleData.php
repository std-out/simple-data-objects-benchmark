<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Spatie;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

final class SimpleData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        #[MapInputName('country_code')]
        public readonly string $countryCode,
    ) {}
}

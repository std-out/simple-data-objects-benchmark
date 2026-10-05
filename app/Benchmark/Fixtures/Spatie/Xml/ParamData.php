<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Spatie\Xml;

use Spatie\LaravelData\Data;

final class ParamData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $value,
    ) {}
}

<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Spatie;

use Spatie\LaravelData\Data;

final class MemberData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
    ) {}
}

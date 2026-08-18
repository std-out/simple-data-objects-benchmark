<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Spatie;

use DateTimeImmutable;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

final class EventData extends Data
{
    public function __construct(
        public readonly string $name,
        #[WithCast(DateTimeInterfaceCast::class, 'Y-m-d')]
        public readonly DateTimeImmutable $startsAt,
    ) {}
}

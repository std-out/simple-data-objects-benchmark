<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Own;

use DateTimeImmutable;
use StdOut\SimpleDataObjects\Attributes\Cast;
use StdOut\SimpleDataObjects\BaseData;
use StdOut\SimpleDataObjects\Casts\DateTimeImmutableCast;

final class EventData extends BaseData
{
    public function __construct(
        public readonly string $name,
        #[Cast(new DateTimeImmutableCast(outputFormat: 'Y-m-d', inputFormat: 'Y-m-d'))]
        public readonly DateTimeImmutable $startsAt,
    ) {}
}

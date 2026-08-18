<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Spatie;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class TeamData extends Data
{
    public function __construct(
        public readonly string $name,
        #[DataCollectionOf(MemberData::class)]
        public readonly DataCollection $members,
    ) {}
}

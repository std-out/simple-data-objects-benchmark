<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Own;

use StdOut\SimpleDataObjects\Attributes\DataCollection;
use StdOut\SimpleDataObjects\BaseData;
use StdOut\SimpleDataObjects\TypedDataCollection;

final class TeamData extends BaseData
{
    public function __construct(
        public readonly string $name,
        #[DataCollection(MemberData::class)]
        public readonly TypedDataCollection $members,
    ) {}
}

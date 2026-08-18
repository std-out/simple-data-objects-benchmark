<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Own;

use StdOut\SimpleDataObjects\BaseData;

final class MemberData extends BaseData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
    ) {}
}

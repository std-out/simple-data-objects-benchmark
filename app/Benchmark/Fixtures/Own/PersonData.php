<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Own;

use StdOut\SimpleDataObjects\BaseData;

final class PersonData extends BaseData
{
    public function __construct(
        public readonly string $name,
        public readonly AddressData $address,
    ) {}
}

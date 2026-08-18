<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Own;

use StdOut\SimpleDataObjects\BaseData;

final class AddressData extends BaseData
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly ?string $zip = null,
    ) {}
}

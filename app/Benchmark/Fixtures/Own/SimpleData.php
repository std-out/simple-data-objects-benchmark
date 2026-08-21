<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Own;

use StdOut\SimpleDataObjects\Attributes\MapInputName;
use StdOut\SimpleDataObjects\BaseData;

final class SimpleData extends BaseData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        #[MapInputName('country_code')]
        public readonly string $countryCode,
    ) {}
}

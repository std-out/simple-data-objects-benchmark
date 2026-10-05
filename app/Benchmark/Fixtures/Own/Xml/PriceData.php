<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Own\Xml;

use StdOut\SimpleDataObjects\Attributes\XmlAttribute;
use StdOut\SimpleDataObjects\Attributes\XmlText;
use StdOut\SimpleDataObjects\BaseData;

final class PriceData extends BaseData
{
    public function __construct(
        #[XmlText]
        public readonly float $amount,
        #[XmlAttribute]
        public readonly string $currency,
    ) {}
}

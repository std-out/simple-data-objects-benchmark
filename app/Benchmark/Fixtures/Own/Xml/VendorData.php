<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Own\Xml;

use StdOut\SimpleDataObjects\Attributes\XmlAttribute;
use StdOut\SimpleDataObjects\BaseData;

final class VendorData extends BaseData
{
    public function __construct(
        #[XmlAttribute]
        public readonly string $code,
        public readonly string $name,
    ) {}
}

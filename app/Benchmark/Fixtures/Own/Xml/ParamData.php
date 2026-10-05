<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Own\Xml;

use StdOut\SimpleDataObjects\Attributes\XmlAttribute;
use StdOut\SimpleDataObjects\Attributes\XmlText;
use StdOut\SimpleDataObjects\BaseData;

final class ParamData extends BaseData
{
    public function __construct(
        #[XmlAttribute]
        public readonly string $name,
        #[XmlText]
        public readonly string $value,
    ) {}
}

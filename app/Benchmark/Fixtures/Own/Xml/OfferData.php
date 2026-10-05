<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Own\Xml;

use StdOut\SimpleDataObjects\Attributes\DataCollection;
use StdOut\SimpleDataObjects\Attributes\XmlAttribute;
use StdOut\SimpleDataObjects\Attributes\XmlElement;
use StdOut\SimpleDataObjects\BaseData;
use StdOut\SimpleDataObjects\TypedDataCollection;

final class OfferData extends BaseData
{
    public function __construct(
        #[XmlAttribute]
        public readonly int $id,
        #[XmlAttribute]
        public readonly bool $available,
        public readonly string $name,
        public readonly PriceData $price,
        #[XmlElement('picture')]
        public readonly array $pictures,
        #[DataCollection(ParamData::class)]
        #[XmlElement('param')]
        public readonly TypedDataCollection $params,
        public readonly ?int $stock = null,
        public readonly ?VendorData $vendor = null,
    ) {}
}

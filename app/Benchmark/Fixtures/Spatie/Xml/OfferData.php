<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Spatie\Xml;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class OfferData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly bool $available,
        public readonly string $name,
        public readonly PriceData $price,
        public readonly array $pictures,
        #[DataCollectionOf(ParamData::class)]
        public readonly DataCollection $params,
        public readonly ?int $stock = null,
        public readonly ?VendorData $vendor = null,
    ) {}
}

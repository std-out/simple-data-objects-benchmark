<?php

declare(strict_types=1);

namespace App\Data;

use StdOut\SimpleDataObjects\Attributes\Computed;
use StdOut\SimpleDataObjects\Attributes\DataCollection;
use StdOut\SimpleDataObjects\Attributes\Hidden;
use StdOut\SimpleDataObjects\Attributes\InferRules;
use StdOut\SimpleDataObjects\Attributes\Pipe;
use StdOut\SimpleDataObjects\Attributes\TransformKeys;
use StdOut\SimpleDataObjects\Attributes\WrapIn;
use StdOut\SimpleDataObjects\BaseData;
use StdOut\SimpleDataObjects\Pipes\NullifyEmptyStringsPipe;
use StdOut\SimpleDataObjects\Pipes\TrimStringsPipe;
use StdOut\SimpleDataObjects\TypedDataCollection;

#[InferRules]
#[Pipe(TrimStringsPipe::class, NullifyEmptyStringsPipe::class)]
#[TransformKeys(TransformKeys::SNAKE_CASE)]
#[WrapIn('data')]
final class OrderData extends AppData
{
    public function __construct(
        public readonly string $number,
        public readonly AddressData $shippingAddress,
        #[DataCollection(OrderLineData::class)]
        public readonly TypedDataCollection $lines,
        #[Hidden]
        public readonly ?string $internalNote = null,
    ) {}

    #[Computed('total')]
    public function total(): float
    {
        return round($this->lines->sum(
            static fn (OrderLineData $line): float => $line->quantity * $line->unitPrice,
        ), 2);
    }
}

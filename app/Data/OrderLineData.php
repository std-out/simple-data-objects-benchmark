<?php

declare(strict_types=1);

namespace App\Data;

use StdOut\SimpleDataObjects\Attributes\Cast;
use StdOut\SimpleDataObjects\Attributes\MapInputName;
use StdOut\SimpleDataObjects\Attributes\Pipe;
use StdOut\SimpleDataObjects\Attributes\Rules;
use StdOut\SimpleDataObjects\BaseData;
use StdOut\SimpleDataObjects\Casts\FloatCast;
use StdOut\SimpleDataObjects\Pipes\TrimValuePipe;

final class OrderLineData extends BaseData
{
    public function __construct(
        #[Pipe(TrimValuePipe::class)]
        #[Rules(['required', 'string', 'max:120'])]
        public readonly string $sku,
        #[Rules(['required', 'integer', 'min:1'])]
        public readonly int $quantity,
        #[MapInputName('unit_price')]
        #[Cast(new FloatCast(2))]
        #[Rules(['required', 'numeric', 'min:0'])]
        public readonly float $unitPrice,
    ) {}
}

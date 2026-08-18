<?php

declare(strict_types=1);

namespace App\Data;

use StdOut\SimpleDataObjects\BaseData;
use StdOut\SimpleDataObjects\Attributes\Rules;

final class AddressData extends BaseData
{
    public function __construct(
        #[Rules(['required', 'string', 'max:120'])]
        public readonly string $street,
        #[Rules(['required', 'string', 'max:80'])]
        public readonly string $city,
        public readonly ?string $postalCode = null,
    ) {}
}

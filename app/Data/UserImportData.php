<?php

declare(strict_types=1);

namespace App\Data;

use DateTimeImmutable;
use StdOut\SimpleDataObjects\Attributes\Cast;
use StdOut\SimpleDataObjects\Attributes\MapInputName;
use StdOut\SimpleDataObjects\BaseData;
use StdOut\SimpleDataObjects\Casts\DateTimeImmutableCast;

final class UserImportData extends BaseData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        #[MapInputName('country_code')]
        public readonly string $countryCode,
        #[MapInputName('registered_at')]
        #[Cast(new DateTimeImmutableCast(outputFormat: 'Y-m-d', inputFormat: 'Y-m-d'))]
        public readonly DateTimeImmutable $registeredAt,
    ) {}
}

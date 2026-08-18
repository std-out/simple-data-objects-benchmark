<?php

declare(strict_types=1);

namespace App\Data;

use DateTimeImmutable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

final class SpatieUserData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        #[MapInputName('country_code')]
        public readonly string $countryCode,
        #[WithCast(DateTimeInterfaceCast::class, 'Y-m-d')]
        public readonly DateTimeImmutable $registeredAt,
    ) {}
}

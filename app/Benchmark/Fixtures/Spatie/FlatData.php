<?php

declare(strict_types=1);

namespace App\Benchmark\Fixtures\Spatie;

use DateTimeImmutable;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

final class FlatData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        #[MapInputName('country_code')]
        public readonly string $countryCode,
        #[MapInputName('registered_at')]
        #[WithCast(DateTimeInterfaceCast::class, 'Y-m-d')]
        public readonly DateTimeImmutable $registeredAt,
    ) {}
}

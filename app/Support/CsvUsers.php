<?php

declare(strict_types=1);

namespace App\Support;

use Generator;
use RuntimeException;

final class CsvUsers
{
    /** @return Generator<int, array<string, string>> */
    public static function rows(string $path): Generator
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("Unable to open CSV: {$path}");
        }

        try {
            $header = fgetcsv($handle);

            if ($header === false || $header === []) {
                throw new RuntimeException('CSV must contain a header row.');
            }

            while (($values = fgetcsv($handle)) !== false) {
                if ($values === [null] || count($values) !== count($header)) {
                    continue;
                }

                /** @var array<string, string> */
                yield array_combine($header, array_map(static fn (mixed $value): string => (string) $value, $values));
            }
        } finally {
            fclose($handle);
        }
    }

    public static function writeSample(string $path, int $rows): void
    {
        $directory = dirname($path);
        is_dir($directory) || mkdir($directory, 0777, true);
        $handle = fopen($path, 'wb');
        fputcsv($handle, ['id', 'name', 'email', 'country_code', 'registered_at']);

        for ($i = 1; $i <= $rows; $i++) {
            fputcsv($handle, [$i, "User {$i}", "user{$i}@example.com", $i % 2 ? 'UA' : 'PL', '2026-01-15']);
        }

        fclose($handle);
    }
}

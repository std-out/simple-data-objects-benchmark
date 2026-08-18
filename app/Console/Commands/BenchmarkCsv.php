<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Data\SpatieUserData;
use App\Data\UserImportData;
use App\Support\CsvUsers;
use Illuminate\Console\Command;
use RuntimeException;

final class BenchmarkCsv extends Command
{
    protected $signature = 'benchmark:csv {path=storage/users.csv} {--iterations=3}';
    protected $description = 'Compare streaming CSV DTO hydration';

    public function handle(): int
    {
        $path = (string) $this->argument('path');
        $path = str_starts_with($path, '/') ? $path : base_path($path);

        if (! is_file($path)) {
            $this->error("CSV not found. Run: php artisan csv:sample {$this->argument('path')}");
            return self::FAILURE;
        }

        $iterations = max(1, (int) $this->option('iterations'));
        $this->line('PHP '.PHP_VERSION.' | file: '.number_format(filesize($path)).' bytes');
        $this->line('Rows are read through a generator; no database or network I/O is included.');
        $this->newLine();

        $this->warmCaches();

        foreach (range(1, $iterations) as $iteration) {
            $own = $this->measureStream(static fn (iterable $rows): iterable => UserImportData::lazyCollection($rows), $path);
            $spatie = $this->measureStream(static fn (iterable $rows): iterable => (static function () use ($rows): \Generator {
                foreach ($rows as $row) {
                    yield SpatieUserData::from($row);
                }
            })(), $path);
            $this->table(['Run', 'DataObject rows/s', 'laravel-data rows/s', 'DataObject faster', 'DataObject peak', 'laravel-data peak', 'DataObject less memory'], [[
                $iteration,
                number_format($own['rows'] / $own['seconds']),
                number_format($spatie['rows'] / $spatie['seconds']),
                self::percent($own['rows'] / $own['seconds'], $spatie['rows'] / $spatie['seconds'], true),
                self::bytes($own['peak']),
                self::bytes($spatie['peak']),
                self::percent((float) $own['peak'], (float) $spatie['peak'], false),
            ]]);
        }

        $this->newLine();
        $this->comment('Interpretation: compare runs on the same PHP build, OS, CPU governor and dependency lockfile.');

        return self::SUCCESS;
    }

    /** @return array{rows: int, seconds: float, peak: int} */
    private function measureStream(callable $factory, string $path): array
    {
        gc_collect_cycles();
        $base = memory_get_usage(true);
        $peak = 0;
        $rows = 0;
        $start = hrtime(true);
        $collection = $factory(CsvUsers::rows($path));

        foreach ($collection as $dto) {
            $rows++;
            $peak = max($peak, memory_get_usage(true) - $base);
        }

        return ['rows' => $rows, 'seconds' => max(0.000001, (hrtime(true) - $start) / 1e9), 'peak' => $peak];
    }

    private function measure(callable $callback): float
    {
        $start = hrtime(true);
        $callback();
        return (hrtime(true) - $start) / 1e6;
    }

    private function warmCaches(): void
    {
        $ownExit = $this->call('sdo:warm', [
            'paths' => [
                base_path('app/Benchmark/Fixtures/Own'),
                base_path('app/Data'),
            ],
            '--cache' => storage_path('framework/cache/data-objects'),
        ]);

        if ($ownExit !== self::SUCCESS) {
            throw new \RuntimeException('sdo:warm failed.');
        }

        config()->set('data.structure_caching.directories', [
            base_path('app/Benchmark/Fixtures/Spatie'),
            base_path('app/Data'),
        ]);

        $spatieExit = $this->call('data:cache-structures');

        if ($spatieExit !== self::SUCCESS) {
            throw new \RuntimeException('data:cache-structures failed.');
        }

        $this->line('DTO metadata and compiled structures warmed for both libraries; measuring steady state.');
    }

    /** @return array<string, string> */
    private static function firstRow(string $path): array
    {
        foreach (CsvUsers::rows($path) as $row) {
            return $row;
        }

        throw new RuntimeException('CSV contains no data rows.');
    }

    private static function bytes(int $bytes): string
    {
        return $bytes >= 1048576 ? number_format($bytes / 1048576, 1).' MB' : number_format($bytes / 1024, 1).' KB';
    }

    private static function percent(float $own, float $other, bool $higherIsBetter): string
    {
        if ($other == 0.0) {
            return 'n/a';
        }

        $value = $higherIsBetter
            ? (($own / $other) - 1) * 100
            : (1 - ($own / $other)) * 100;

        return ($value >= 0 ? '+' : '').number_format($value, 1).'%';
    }
}

<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Benchmark\Fixtures\Own\EventData as OwnEventData;
use App\Benchmark\Fixtures\Own\FlatData as OwnFlatData;
use App\Benchmark\Fixtures\Own\PersonData as OwnPersonData;
use App\Benchmark\Fixtures\Own\SimpleData as OwnSimpleData;
use App\Benchmark\Fixtures\Own\TeamData as OwnTeamData;
use App\Benchmark\Fixtures\Spatie\EventData as SpatieEventData;
use App\Benchmark\Fixtures\Spatie\FlatData as SpatieFlatData;
use App\Benchmark\Fixtures\Spatie\PersonData as SpatiePersonData;
use App\Benchmark\Fixtures\Spatie\SimpleData as SpatieSimpleData;
use App\Benchmark\Fixtures\Spatie\TeamData as SpatieTeamData;
use App\Support\CsvUsers;
use Illuminate\Console\Command;

final class BenchmarkAll extends Command
{
    protected $signature = 'benchmark:all {path=storage/users.csv} {--iterations=20000}';
    protected $description = 'Run equivalent SDO and laravel-data benchmarks';

    public function handle(): int
    {
        $path = str_starts_with((string) $this->argument('path'), '/')
            ? (string) $this->argument('path')
            : base_path((string) $this->argument('path'));

        if (! is_file($path)) {
            CsvUsers::writeSample($path, 100_000);
        }

        $iterations = max(100, (int) $this->option('iterations'));
        $warmup = max(10, intdiv($iterations, 10));
        // Build a fresh input array (including nested arrays) for every DTO
        // invocation so the benchmark does not reuse one input object.
        $simple = static fn (): array => ['id' => 1, 'name' => 'Ada Lovelace', 'email' => 'ada@example.com', 'country_code' => 'GB'];
        $nested = static fn (): array => ['name' => 'Ada Lovelace', 'address' => ['street' => '12 Analytical Engine Ave', 'city' => 'London', 'zip' => 'SW1A 1AA']];
        $team = static fn (): array => [
            'name' => 'Engineering',
            'members' => array_map(static fn (int $i): array => ['name' => "Member {$i}", 'email' => "member{$i}@example.com"], range(1, 20)),
        ];
        $event = static fn (): array => ['name' => 'PHP Conference', 'startsAt' => '2026-01-15'];

        $this->info('PHP '.PHP_VERSION.' | iterations: '.number_format($iterations).' | warmup: '.number_format($warmup));
        $this->line('Same payloads, equivalent DTO shapes, same process and Laravel runtime.');
        $this->newLine();
        $this->warmCaches();

        $this->table(['Scenario', 'DataObject ops/s', 'laravel-data ops/s', 'DataObject advantage', 'Fastest'], [
            $this->row('Hydration: flat', fn () => OwnSimpleData::from($simple()), fn () => SpatieSimpleData::from($simple()), $iterations, $warmup),
            $this->row('Hydration: nested', fn () => OwnPersonData::from($nested()), fn () => SpatiePersonData::from($nested()), $iterations, $warmup),
            $this->row('Hydration: collection (20)', fn () => OwnTeamData::from($team()), fn () => SpatieTeamData::from($team()), $iterations, $warmup),
            $this->row('Hydration: cast', fn () => OwnEventData::from($event()), fn () => SpatieEventData::from($event()), $iterations, $warmup),
        ]);

        $ownSimple = OwnSimpleData::from($simple());
        $spatieSimple = SpatieSimpleData::from($simple());
        $ownPerson = OwnPersonData::from($nested());
        $spatiePerson = SpatiePersonData::from($nested());
        $ownTeam = OwnTeamData::from($team());
        $spatieTeam = SpatieTeamData::from($team());

        $this->table(['Scenario', 'DataObject ops/s', 'laravel-data ops/s', 'DataObject advantage', 'Fastest'], [
            $this->row('Serialization: flat', fn () => $ownSimple->toArray(), fn () => $spatieSimple->toArray(), $iterations, $warmup),
            $this->row('Serialization: nested', fn () => $ownPerson->toArray(), fn () => $spatiePerson->toArray(), $iterations, $warmup),
            $this->row('Serialization: collection (20)', fn () => $ownTeam->toArray(), fn () => $spatieTeam->toArray(), $iterations, $warmup),
        ]);

        $this->table(['Scenario', 'DataObject CPU us/op', 'laravel-data CPU us/op', 'DataObject advantage', 'Lowest'], [
            $this->cpuRow('CPU: flat hydration', fn () => OwnSimpleData::from($simple()), fn () => SpatieSimpleData::from($simple()), $iterations, $warmup),
            $this->cpuRow('CPU: nested hydration', fn () => OwnPersonData::from($nested()), fn () => SpatiePersonData::from($nested()), $iterations, $warmup),
            $this->cpuRow('CPU: collection hydration', fn () => OwnTeamData::from($team()), fn () => SpatieTeamData::from($team()), $iterations, $warmup),
            $this->cpuRow('CPU: date cast', fn () => OwnEventData::from($event()), fn () => SpatieEventData::from($event()), $iterations, $warmup),
            $this->cpuRow('CPU: flat serialization', fn () => $ownSimple->toArray(), fn () => $spatieSimple->toArray(), $iterations, $warmup),
            $this->cpuRow('CPU: nested serialization', fn () => $ownPerson->toArray(), fn () => $spatiePerson->toArray(), $iterations, $warmup),
            $this->cpuRow('CPU: collection serialization', fn () => $ownTeam->toArray(), fn () => $spatieTeam->toArray(), $iterations, $warmup),
        ]);

        $this->table(['Scenario', 'DataObject bytes/op', 'laravel-data bytes/op', 'DataObject advantage', 'Lowest'], [
            $this->memoryRow('Memory: flat hydration', fn () => OwnSimpleData::from($simple()), fn () => SpatieSimpleData::from($simple())),
            $this->memoryRow('Memory: nested hydration', fn () => OwnPersonData::from($nested()), fn () => SpatiePersonData::from($nested())),
            $this->memoryRow('Memory: collection hydration', fn () => OwnTeamData::from($team()), fn () => SpatieTeamData::from($team())),
        ]);

        $this->table(['Scenario', 'DataObject rows/s', 'laravel-data rows/s', 'DataObject faster', 'DataObject peak', 'laravel-data peak', 'DataObject less memory'], [
            $this->streamRow('CSV streaming: 100k rows', $path),
        ]);

        $this->newLine();
        $this->comment('Numbers are machine-specific. Publish medians from 5+ runs with PHP, OS, CPU and lockfile.');

        return self::SUCCESS;
    }

    /** @return array<int, string> */
    private function row(string $name, callable $own, callable $spatie, int $iterations, int $warmup): array
    {
        $a = $this->measure($own, $iterations, $warmup);
        $b = $this->measure($spatie, $iterations, $warmup);
        return [$name, number_format($a), number_format($b), $this->percent($a, $b, true), $a >= $b ? 'DataObject' : 'laravel-data'];
    }

    /** @return array<int, string> */
    private function memoryRow(string $name, callable $own, callable $spatie): array
    {
        $a = $this->memory($own);
        $b = $this->memory($spatie);
        return [$name, number_format($a), number_format($b), $this->percent($a, $b, false), $a <= $b ? 'DataObject' : 'laravel-data'];
    }

    /** @return array<int, string> */
    private function cpuRow(string $name, callable $own, callable $spatie, int $iterations, int $warmup): array
    {
        $a = $this->cpu($own, $iterations, $warmup);
        $b = $this->cpu($spatie, $iterations, $warmup);
        return [$name, number_format($a, 2), number_format($b, 2), $this->percent($a, $b, false), $a <= $b ? 'DataObject' : 'laravel-data'];
    }

    /** @return array<int, string> */
    private function streamRow(string $name, string $path): array
    {
        $ownFactory = static fn (iterable $rows): iterable => OwnFlatData::lazyCollection($rows);
        $spatieFactory = static fn (iterable $rows): iterable => (static function () use ($rows): \Generator {
            foreach ($rows as $row) {
                yield SpatieFlatData::from($row);
            }
        })();

        // Neither factory has been exercised yet at this point (Hydration/Serialization/CPU/Memory
        // rows above use the cast-free SimpleData fixture, not FlatData). Without this warmup, whichever
        // factory runs first below pays a one-off class-loading/first-execution memory tax that has
        // nothing to do with the library — it would look like a real allocation difference otherwise.
        $this->warmStream($ownFactory, $path);
        $this->warmStream($spatieFactory, $path);

        $a = $this->stream($ownFactory, $path);
        $b = $this->stream($spatieFactory, $path);
        $ownRowsPerSecond = $a['rows'] / $a['seconds'];
        $spatieRowsPerSecond = $b['rows'] / $b['seconds'];
        return [$name, number_format($ownRowsPerSecond), number_format($spatieRowsPerSecond), $this->percent($ownRowsPerSecond, $spatieRowsPerSecond, true), $this->bytes($a['peak']), $this->bytes($b['peak']), $this->percent((float) $a['peak'], (float) $b['peak'], false)];
    }

    private function measure(callable $fn, int $iterations, int $warmup): float
    {
        for ($i = 0; $i < $warmup; $i++) { $fn(); }
        $start = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) { $fn(); }
        return $iterations / ((hrtime(true) - $start) / 1e9);
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

    private function milliseconds(callable $fn): float
    {
        $start = hrtime(true);
        $fn();
        return (hrtime(true) - $start) / 1e6;
    }

    private function cpu(callable $fn, int $iterations, int $warmup): float
    {
        for ($i = 0; $i < $warmup; $i++) { $fn(); }
        $before = getrusage();
        for ($i = 0; $i < $iterations; $i++) { $fn(); }
        $after = getrusage();
        $seconds = ($after['ru_utime.tv_sec'] - $before['ru_utime.tv_sec'])
            + (($after['ru_utime.tv_usec'] - $before['ru_utime.tv_usec']) / 1e6)
            + ($after['ru_stime.tv_sec'] - $before['ru_stime.tv_sec'])
            + (($after['ru_stime.tv_usec'] - $before['ru_stime.tv_usec']) / 1e6);
        return ($seconds * 1e6) / $iterations;
    }

    private function memory(callable $fn): int
    {
        gc_collect_cycles();
        $before = memory_get_usage(); $keep = [];
        for ($i = 0; $i < 1000; $i++) { $keep[] = $fn(); }
        $bytes = max(0, memory_get_usage() - $before);
        unset($keep);
        return (int) round($bytes / 1000);
    }

    /** @return array{rows: int, seconds: float, peak: int} */
    private function stream(callable $factory, string $path): array
    {
        gc_collect_cycles(); $base = memory_get_usage(); $peak = 0; $rows = 0; $start = hrtime(true);
        foreach ($factory(CsvUsers::rows($path)) as $_) { $rows++; $peak = max($peak, memory_get_usage() - $base); }
        return ['rows' => $rows, 'seconds' => max(0.000001, (hrtime(true) - $start) / 1e9), 'peak' => $peak];
    }

    private function warmStream(callable $factory, string $path, int $rows = 500): void
    {
        $n = 0;
        foreach ($factory(CsvUsers::rows($path)) as $_) {
            if (++$n >= $rows) {
                break;
            }
        }
    }

    private function bytes(int $bytes): string
    {
        return $bytes >= 1048576 ? number_format($bytes / 1048576, 1).' MB' : number_format($bytes / 1024, 1).' KB';
    }

    private function percent(float $own, float $other, bool $higherIsBetter): string
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

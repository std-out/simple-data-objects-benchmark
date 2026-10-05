<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Benchmark\Fixtures\Own\Xml\OfferData as OwnOfferData;
use App\Benchmark\Fixtures\Spatie\Xml\OfferData as SpatieOfferData;
use App\Support\XmlOffers;
use Closure;
use Illuminate\Console\Command;

final class BenchmarkXml extends Command
{
    protected $signature = 'benchmark:xml {path=storage/offers.xml} {--scenario= : Internal — run one scenario and print its result as JSON}';
    protected $description = 'Compare ways of turning a large XML feed into DTOs';

    public function handle(): int
    {
        $path = (string) $this->argument('path');
        $path = str_starts_with($path, '/') ? $path : base_path($path);

        if (! is_file($path)) {
            $this->error("XML not found. Run: php artisan xml:sample {$this->argument('path')}");

            return self::FAILURE;
        }

        $scenarios = $this->scenarios();

        if ($this->option('scenario') !== null) {
            $start = hrtime(true);
            $checksum = (array_values($scenarios)[(int) $this->option('scenario')] ?? static fn (): array => [0, 0])($path);

            $this->output->write(json_encode([
                'seconds' => (hrtime(true) - $start) / 1e9,
                'heap' => memory_get_peak_usage(),
                'rss' => getrusage()['ru_maxrss'] * 1024,
                'checksum' => $checksum,
            ]));

            return self::SUCCESS;
        }

        $this->line('PHP '.PHP_VERSION.' | file: '.number_format(filesize($path) / 1048576, 1).' MB');
        $this->line('Each scenario runs in its own process: peak RSS also counts what libxml allocates');
        $this->line('outside PHP\'s memory manager, which memory_get_peak_usage() (the heap column) cannot see.');
        $this->line('The baseline row is what the booted Laravel process costs before any XML is read.');
        $this->line('No database or network I/O is included.');
        $this->newLine();

        $baseline = $this->runScenario(-1, $path);
        $rows = [[
            'Baseline: booted app, nothing parsed',
            '—',
            '—',
            number_format($baseline['heap'] / 1048576, 1).' MB',
            number_format($baseline['rss'] / 1048576, 1).' MB',
        ]];
        $checksums = [];

        foreach (array_keys($scenarios) as $index => $label) {
            $result = $this->runScenario($index, $path);

            if ($result === null) {
                $this->error("Scenario failed: {$label}");

                return self::FAILURE;
            }

            $checksums[$result['checksum']] = true;
            $nodes ??= $result['nodes'];

            $rows[] = [
                $label,
                number_format($result['seconds'], 2).' s',
                number_format($result['nodes'] / $result['seconds']),
                number_format($result['heap'] / 1048576, 1).' MB',
                number_format($result['rss'] / 1048576, 1).' MB',
            ];
        }

        $this->table(['Scenario', 'Time', 'Nodes/s', 'PHP heap (peak)', 'Process RSS (peak)'], $rows);

        if (count($checksums) !== 1) {
            $this->error('Checksum mismatch: the scenarios did not produce the same data.');

            return self::FAILURE;
        }

        $this->comment('All scenarios produced the same checksum over '.number_format($nodes).' offers.');
        $this->comment('Interpretation: compare runs on the same PHP build, OS, CPU governor and dependency lockfile.');

        return self::SUCCESS;
    }

    /** @return array{seconds: float, heap: int, rss: int, checksum: int, nodes: int}|null */
    private function runScenario(int $index, string $path): ?array
    {
        $command = implode(' ', [
            escapeshellarg(PHP_BINARY),
            '-d memory_limit=-1',
            escapeshellarg(base_path('artisan')),
            'benchmark:xml',
            escapeshellarg($path),
            "--scenario={$index}",
        ]);

        $result = json_decode((string) shell_exec($command), true);

        return is_array($result) ? [...$result, 'nodes' => $result['checksum'][1], 'checksum' => $result['checksum'][0]] : null;
    }

    /**
     * Every scenario ends with the same typed objects and returns
     * [checksum, node count], so only the way the XML gets there differs.
     *
     * @return array<string, Closure(string): array{0: int, 1: int}>
     */
    private function scenarios(): array
    {
        $consume = static function (iterable $offers): array {
            $checksum = 0;
            $nodes = 0;

            foreach ($offers as $offer) {
                $checksum += $offer->id + count($offer->params) + (int) $offer->price->amount;
                $nodes++;
            }

            return [$checksum, $nodes];
        };

        $simpleXmlRows = static function (string $path): array {
            $rows = [];

            foreach (simplexml_load_file($path)->shop->offers->offer as $node) {
                $rows[] = XmlOffers::toArray($node);
            }

            return $rows;
        };

        return [
            'DataObject lazyXml() (streaming)' => static fn (string $path): array => $consume(OwnOfferData::lazyXml($path, XmlOffers::PATH)),
            'DataObject SimpleXML, collect all, collection()' => static fn (string $path): array => $consume(OwnOfferData::collection($simpleXmlRows($path))),
            'DataObject SimpleXML, node by node, from()' => static fn (string $path): array => $consume((static function () use ($path): \Generator {
                foreach (simplexml_load_file($path)->shop->offers->offer as $node) {
                    yield OwnOfferData::from(XmlOffers::toArray($node));
                }
            })()),
            'DataObject hand-rolled XMLReader + from()' => static fn (string $path): array => $consume((static function () use ($path): \Generator {
                foreach (XmlOffers::stream($path) as $row) {
                    yield OwnOfferData::from($row);
                }
            })()),
            'laravel-data hand-rolled XMLReader + from()' => static fn (string $path): array => $consume((static function () use ($path): \Generator {
                foreach (XmlOffers::stream($path) as $row) {
                    yield SpatieOfferData::from($row);
                }
            })()),
            'laravel-data SimpleXML, collect all, collect()' => static fn (string $path): array => $consume(SpatieOfferData::collect($simpleXmlRows($path))),
        ];
    }
}

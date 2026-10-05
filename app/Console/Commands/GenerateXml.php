<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\XmlOffers;
use Illuminate\Console\Command;

final class GenerateXml extends Command
{
    protected $signature = 'xml:sample {path=storage/offers.xml} {--nodes=100000}';
    protected $description = 'Generate a deterministic XML price-feed fixture for the benchmark';

    public function handle(): int
    {
        $path = (string) $this->argument('path');
        $path = str_starts_with($path, '/') ? $path : base_path($path);
        $nodes = max(1, (int) $this->option('nodes'));
        XmlOffers::writeSample($path, $nodes);
        $this->info("Wrote {$nodes} offers to {$path}");

        return self::SUCCESS;
    }
}

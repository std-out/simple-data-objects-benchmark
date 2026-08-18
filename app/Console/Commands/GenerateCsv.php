<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\CsvUsers;
use Illuminate\Console\Command;

final class GenerateCsv extends Command
{
    protected $signature = 'csv:sample {path=storage/users.csv} {--rows=100000}';
    protected $description = 'Generate a deterministic CSV fixture for the benchmark';

    public function handle(): int
    {
        $path = (string) $this->argument('path');
        $path = str_starts_with($path, '/') ? $path : base_path($path);
        $rows = max(1, (int) $this->option('rows'));
        CsvUsers::writeSample($path, $rows);
        $this->info("Wrote {$rows} rows to {$path}");

        return self::SUCCESS;
    }
}

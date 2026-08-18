<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about-benchmark', static function (): void {
    $this->comment('See: php artisan benchmark:csv --help');
});

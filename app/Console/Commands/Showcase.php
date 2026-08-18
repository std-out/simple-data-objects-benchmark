<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Data\OrderData;
use App\Data\UserImportData;
use Illuminate\Console\Command;

final class Showcase extends Command
{
    protected $signature = 'showcase:data';
    protected $description = 'Run a small tour of the library capabilities';

    public function handle(): int
    {
        $order = OrderData::from([
            'number' => '  INV-1001 ',
            'shipping_address' => ['street' => '1 Main St', 'city' => 'Kyiv'],
            'lines' => [
                ['sku' => ' SKU-1 ', 'quantity' => '2', 'unit_price' => '19.995'],
            ],
            'internal_note' => 'not serialized',
        ]);

        $this->line('Hydration + pipes + nested typed collection:');
        $this->line(json_encode($order->toArray(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        $this->line('Immutable update: '.$order->with(number: 'INV-1002')->number);
        $this->line('JSON Schema keys: '.implode(', ', array_keys(OrderData::jsonSchema())));
        $this->line('Input normalization: '.UserImportData::from([
            'id' => '1', 'name' => 'Ada', 'email' => 'ada@example.com',
            'country_code' => 'UA', 'registered_at' => '2026-01-15',
        ])->registeredAt->format('Y-m-d'));

        return self::SUCCESS;
    }
}

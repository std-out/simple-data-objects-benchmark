# Simple Data Objects vs Laravel Data — Benchmark

Runnable companion project comparing:

- [`std-out/simple-data-objects`](https://github.com/std-out/simple-data-objects)
- [`spatie/laravel-data`](https://github.com/spatie/laravel-data)

## Run it

Requirements: PHP 8.4+, Composer, and the `mbstring`/`xml` extensions required by Laravel.

```sh
composer install
php artisan csv:sample storage/users.csv --rows=100000
php artisan benchmark:all storage/users.csv --iterations=5
```

Or run everything in Docker — this suite followed by the
[XML feed benchmark](#xml-feed-import):

```sh
make bench
```

`benchmark:all` compares both libraries on flat and nested hydration, a 20-item
typed collection, date casts, flat/nested/collection serialization, retained
memory, and 100,000-row streaming CSV hydration. `benchmark:csv` runs just the
CSV scenario.

Before measuring, it runs both libraries' cache-warming commands (`sdo:warm`
and `data:cache-structures`), then reports steady-state rows/sec and peak
memory. Run it a few times on the same machine and use the median.

## XML feed import

```sh
php artisan xml:sample storage/offers.xml --nodes=100000
php artisan benchmark:xml storage/offers.xml
```

Or in Docker: `make bench-xml` for this benchmark alone (`make bench` runs it after the main suite).

`benchmark:xml` reads the 100,000-offer price feed written by `xml:sample`
(about 52 MB) and turns it into the same typed DTOs six different ways:

- `lazyXml()` — the DTO describes the element, the file is streamed;
- SimpleXML, collecting every row before hydrating (both libraries);
- SimpleXML, hydrating node by node without accumulating anything;
- a hand-rolled `XMLReader` loop with a hand-written element-to-array
  mapping (both libraries — `spatie/laravel-data` has no XML support of its
  own, so this is what streaming looks like there).

Each scenario runs in its own PHP process and reports time, peak PHP heap
and peak process RSS. The two memory columns differ on purpose: the libxml
tree behind SimpleXML is allocated outside PHP's memory manager, so
`memory_get_peak_usage()` does not see it while the process RSS does. A
checksum over all offers confirms every scenario produced the same data.

## Other commands

```sh
php artisan showcase:data
php artisan sdo:typescript app/Data --output=resources/js/types/data-objects.d.ts
```

`showcase:data` demonstrates nested DTOs, typed collections, casts, pipes,
computed/hidden fields, key transformation, schema generation, and immutable
updates. `sdo:typescript` generates a TypeScript contract from the same DTO
metadata.

## Publishing results

For a fair comparison, commit `composer.lock` and record the PHP/Laravel/
package versions and hardware alongside the numbers.

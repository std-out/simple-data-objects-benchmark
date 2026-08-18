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

Or run the same suite in Docker:

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

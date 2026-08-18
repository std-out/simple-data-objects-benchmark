.PHONY: build install sample bench showcase types shell clean

build:
	docker compose build

install:
	docker compose run --rm benchmark composer install --no-interaction --prefer-dist

sample:
	docker compose run --rm benchmark php artisan csv:sample storage/users.csv --rows=100000

bench:
	docker compose run --rm benchmark sh -c "php artisan csv:sample storage/users.csv --rows=100000 && php artisan benchmark:all"

showcase:
	docker compose run --rm benchmark php artisan showcase:data

types:
	docker compose run --rm benchmark php artisan sdo:typescript app/Data --output=resources/js/types/data-objects.d.ts

shell:
	docker compose run --rm benchmark sh

clean:
	docker compose down --volumes --remove-orphans

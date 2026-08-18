FROM php:8.4-cli-alpine

RUN apk add --no-cache git unzip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# php:*-cli-alpine ships opcache disabled for the CLI SAPI and JIT off by
# default — without this, the benchmark measures the Zend interpreter, not
# the compiled/OPcache-warmed path the article's numbers describe.
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.jit=tracing'; \
    echo 'opcache.jit_buffer_size=64M'; \
    } > /usr/local/etc/php/conf.d/opcache-cli.ini

WORKDIR /app/pet-benchmark

CMD ["php", "artisan", "benchmark:all"]

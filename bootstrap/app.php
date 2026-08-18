<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use StdOut\SimpleDataObjects\Laravel\SimpleDataObjectsServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([SimpleDataObjectsServiceProvider::class])
    ->withRouting(commands: __DIR__.'/../routes/console.php')
    ->withCommands([__DIR__.'/../app/Console/Commands'])
    ->withMiddleware(fn (Middleware $middleware) => null)
    ->withExceptions(fn (Exceptions $exceptions) => null)
    ->create();

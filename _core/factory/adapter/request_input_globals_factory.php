<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class request_input_globals_factory
{
    private \Closure $environmentFactory;

    private \Closure $globalsProvider;

    public function __construct(callable $environmentFactory, ?callable $globalsProvider = null)
    {
        $this->environmentFactory = \Closure::fromCallable($environmentFactory);
        $this->globalsProvider = \Closure::fromCallable(
            $globalsProvider
                ?? static fn(object $environment): object => new request_input_globals($environment)
        );
    }

    public function __invoke(): object
    {
        $environment = ($this->environmentFactory)();
        if (!is_object($environment)) {
            throw new \RuntimeException('Request input environment factory must return an object.');
        }

        $globalsProvider = $this->globalsProvider;

        return $globalsProvider($environment);
    }
}

<?php

declare(strict_types=1);

namespace fan\core\di;

final class bootstrap_autoloader_defaults_factory
{
    private \Closure $zendAutoloaderLoaderFactory;

    public function __construct(callable $zendAutoloaderLoaderFactory)
    {
        $this->zendAutoloaderLoaderFactory = \Closure::fromCallable($zendAutoloaderLoaderFactory);
    }

    public function zendAutoloaderLoader(): object
    {
        return ($this->zendAutoloaderLoaderFactory)();
    }
}

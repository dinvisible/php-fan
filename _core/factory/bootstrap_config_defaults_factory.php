<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\bootstrap\bootstrap_config_loader;

final class bootstrap_config_defaults_factory
{
    private \Closure $configLoaderFactory;

    public function __construct(?callable $configLoaderFactory = null)
    {
        $this->configLoaderFactory = \Closure::fromCallable(
            $configLoaderFactory
                ?? static fn(): callable => new bootstrap_config_loader()
        );
    }

    public function configLoader(): callable
    {
        return ($this->configLoaderFactory)();
    }
}

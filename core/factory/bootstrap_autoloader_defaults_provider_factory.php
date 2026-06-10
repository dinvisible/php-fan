<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\di\bootstrap_autoloader_defaults_factory;
use fan\core\adapter\zend_autoloader_loader;


final class bootstrap_autoloader_defaults_provider_factory
{
    private \Closure $zendAutoloaderLoaderFactoryProvider;

    public function __construct(?callable $zendAutoloaderLoaderFactoryProvider = null)
    {
        $this->zendAutoloaderLoaderFactoryProvider = \Closure::fromCallable(
            $zendAutoloaderLoaderFactoryProvider
                ?? static fn(): object => new zend_autoloader_loader()
        );
    }

    public function __invoke(): bootstrap_autoloader_defaults_factory
    {
        $zendAutoloaderLoaderFactoryProvider = $this->zendAutoloaderLoaderFactoryProvider;

        return new bootstrap_autoloader_defaults_factory(
            $zendAutoloaderLoaderFactoryProvider
        );
    }
}

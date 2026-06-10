<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\di\bootstrap_loader_defaults_factory;
use fan\core\adapter\bootstrap_loader_file_storage;


final class bootstrap_loader_defaults_provider_factory
{
    private \Closure $fileStorageFactoryProvider;

    public function __construct(?callable $fileStorageFactoryProvider = null)
    {
        $this->fileStorageFactoryProvider = \Closure::fromCallable(
            $fileStorageFactoryProvider
                ?? static fn(): object => new bootstrap_loader_file_storage()
        );
    }

    public function __invoke(): bootstrap_loader_defaults_factory
    {
        $fileStorageFactoryProvider = $this->fileStorageFactoryProvider;

        return new bootstrap_loader_defaults_factory(
            $fileStorageFactoryProvider
        );
    }
}

<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_service_factory_defaults_provider
{
    private \Closure $configServiceFactoryFactory;
    private \Closure $fileSystemServiceFactoryFactory;
    private \Closure $jsonServiceFactoryFactory;
    private \Closure $cacheServiceFactoryFactory;

    public function __construct(
        callable $configServiceFactoryFactory,
        callable $fileSystemServiceFactoryFactory,
        callable $jsonServiceFactoryFactory,
        callable $cacheServiceFactoryFactory
    ) {
        $this->configServiceFactoryFactory = \Closure::fromCallable($configServiceFactoryFactory);
        $this->fileSystemServiceFactoryFactory = \Closure::fromCallable($fileSystemServiceFactoryFactory);
        $this->jsonServiceFactoryFactory = \Closure::fromCallable($jsonServiceFactoryFactory);
        $this->cacheServiceFactoryFactory = \Closure::fromCallable($cacheServiceFactoryFactory);
    }

    public function configServiceFactory(): callable
    {
        return $this->configServiceFactoryFactory;
    }

    public function fileSystemServiceFactory(): callable
    {
        return $this->fileSystemServiceFactoryFactory;
    }

    public function jsonServiceFactory(): callable
    {
        return $this->jsonServiceFactoryFactory;
    }

    public function cacheServiceFactory(): callable
    {
        return $this->cacheServiceFactoryFactory;
    }
}

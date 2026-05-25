<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\file_system as core_file_system_service;

final class file_system_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        string $fullPath,
        object $storage,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory
    ): object {
        $arguments = [
            $fullPath,
            $storage,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_file_system_service::class,
            $arguments,
            $arguments
        );
    }

}

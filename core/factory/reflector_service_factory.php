<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\reflector as core_reflector_service;

final class reflector_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        object $reflectionClassFactory
    ): mixed {
        $arguments = [
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $reflectionClassFactory,
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_reflector_service::class,
            $arguments,
            $arguments
        );
    }

}

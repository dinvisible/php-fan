<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\rest as core_rest_service;

final class rest_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        ?string $connectionName,
        callable $jsonFactory,
        callable $curlFactory,
        callable $errorFactory,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory
    ): mixed {
        $arguments = [
            $connectionName,
            $jsonFactory,
            $curlFactory,
            $errorFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_rest_service::class,
            $arguments,
            $arguments
        );
    }

}

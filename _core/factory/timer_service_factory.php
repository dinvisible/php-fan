<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\timer as core_timer_service;

final class timer_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        object $runtime,
        callable $dateFactory,
        callable $entityFactory,
        callable $errorFactory,
        ?callable $logFactory,
        ?callable $emailFactory,
        callable $programFactory,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory
    ): mixed {
        $arguments = [
            $runtime,
            $dateFactory,
            $entityFactory,
            $errorFactory,
            $logFactory,
            $emailFactory,
            $programFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_timer_service::class,
            $arguments,
            $arguments
        );
    }

}

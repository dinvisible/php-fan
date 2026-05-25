<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\role as core_role_service;

final class role_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        callable $currentUserFactory,
        callable $sessionFactory,
        object $errorLogger,
        callable $userSpaceProvider,
        callable $dateFactory,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory
    ): mixed {
        $arguments = [
            $currentUserFactory,
            $sessionFactory,
            $errorLogger,
            $userSpaceProvider,
            $dateFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_role_service::class,
            $arguments,
            $arguments
        );
    }

}

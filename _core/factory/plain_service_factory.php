<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\plain as core_plain_service;

final class plain_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        bool $allowIni,
        object $matcher,
        callable $plainConfigFactory,
        object $header,
        callable $controllerDependenciesFactory,
        callable $controllerFactory,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory
    ): mixed {
        $arguments = [
            $allowIni,
            $matcher,
            $plainConfigFactory,
            $header,
            $controllerDependenciesFactory,
            $controllerFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_plain_service::class,
            $arguments,
            $arguments
        );
    }

}

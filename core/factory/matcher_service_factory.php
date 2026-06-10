<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\matcher as core_matcher_service;

final class matcher_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        bool $allowIni,
        object $input,
        object $runtime,
        object $locale,
        object $application,
        object $routeFileStorage,
        callable $itemFactory,
        callable $itemComponentFactory,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory
    ): object {
        $arguments = [
            $allowIni,
            $input,
            $runtime,
            $locale,
            $application,
            $routeFileStorage,
            $itemFactory,
            $itemComponentFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_matcher_service::class,
            $arguments,
            $arguments
        );
    }

}

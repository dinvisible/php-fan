<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\application as core_application_service;

final class application_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        bool $allowIni,
        object $runtime,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        ?callable $arrayAdducer = null
    ): mixed {
        $arguments = [
            $allowIni,
            $runtime,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $arrayAdducer ?? static fn(mixed $value): array => \adduceToArray($value),
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_application_service::class,
            $arguments,
            $arguments
        );
    }

}

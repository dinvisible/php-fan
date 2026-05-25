<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\debug as core_debug_service;

final class debug_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        bool $allowIni,
        object $tab,
        object $input,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        object $metaFileStorage,
        ?callable $arrayAdducer = null,
        ?object $reflectionClassFactory = null
    ): mixed {
        $arguments = [
            $allowIni,
            $tab,
            $input,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $metaFileStorage,
            $arrayAdducer ?? static fn(mixed $value): array => \adduceToArray($value),
            $reflectionClassFactory,
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_debug_service::class,
            $arguments,
            $arguments
        );
    }

}

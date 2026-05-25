<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\header as core_header_service;

final class header_service_factory
{
    private service_factory_map $serviceFactoryMap;

    private \Closure $recursiveMerger;

    public function __construct(
        callable $configuredServiceFactory,
        ?callable $recursiveMerger = null
    )
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
        $this->recursiveMerger = \Closure::fromCallable($recursiveMerger ?? static fn(mixed ...$values): mixed => \array_merge_recursive_alt(...$values));
    }

    public function __invoke(
        string $className,
        bool $allowIni,
        object $input,
        object $headerWriter,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        ?callable $recursiveMerger = null
    ): mixed {
        $arguments = [
            $allowIni,
            $input,
            $headerWriter,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $recursiveMerger ?? $this->recursiveMerger,
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_header_service::class,
            $arguments,
            $arguments
        );
    }

}

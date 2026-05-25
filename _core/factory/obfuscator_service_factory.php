<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\obfuscator as core_obfuscator_service;

final class obfuscator_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        string $type,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        callable $phpArrayFileLoader,
        object $fileStorage
    ): mixed {
        $arguments = [
            $type,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $phpArrayFileLoader,
            $fileStorage
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_obfuscator_service::class,
            $arguments,
            $arguments
        );
    }

}

<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\cache as core_cache_service;

final class cache_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        string $type,
        object $runtime,
        callable $errorFactory,
        object $cacheState,
        object $memcacheState,
        callable $cacheEngineFactory,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?object $sourceFileMetadata = null,
        ?callable $configCacheFatalExceptionFactory = null
    ): object {
        $arguments = [
            $type,
            $runtime,
            $errorFactory,
            $cacheState,
            $memcacheState,
            $cacheEngineFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $sourceFileMetadata,
            $configCacheFatalExceptionFactory
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_cache_service::class,
            $arguments,
            $arguments
        );
    }

}

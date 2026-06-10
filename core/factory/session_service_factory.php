<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\session as core_session_service;

final class session_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        string $nameSpace,
        string $group,
        ?object $databaseConfig,
        ?object $requestInput,
        callable $errorFactory,
        ?object $requestService,
        ?object $logService,
        callable $sessionFactory,
        callable $dateFactory,
        callable $cookieFactory,
        ?object $pearSessionSupportLoader,
        callable $sessionEngineFactory,
        object $sessionState,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        object $phpRuntimeSettings,
        object $nativeSession,
        ?callable $arrayValueReader = null
    ): object {
        $arguments = [
            $nameSpace,
            $group,
            $databaseConfig,
            $requestInput,
            $errorFactory,
            $requestService,
            $logService,
            $sessionFactory,
            $dateFactory,
            $cookieFactory,
            $pearSessionSupportLoader,
            $sessionEngineFactory,
            $sessionState,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $phpRuntimeSettings,
            $nativeSession,
            $arrayValueReader ?? static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default)
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_session_service::class,
            $arguments,
            $arguments
        );
    }

}

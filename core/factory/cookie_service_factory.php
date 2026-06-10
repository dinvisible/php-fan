<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\cookie as core_cookie_service;

final class cookie_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        mixed $path,
        mixed $domain,
        bool $secure,
        object $requestInput,
        callable $errorFactory,
        callable $cookieValueEncoder,
        callable $cookieValueDecoder,
        callable $cookieValueChecker,
        object $cookieWriter,
        object $state,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory
    ): mixed {
        $arguments = [
            $path,
            $domain,
            $secure,
            $requestInput,
            $errorFactory,
            $cookieValueEncoder,
            $cookieValueDecoder,
            $cookieValueChecker,
            $cookieWriter,
            $state,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_cookie_service::class,
            $arguments,
            $arguments
        );
    }

}

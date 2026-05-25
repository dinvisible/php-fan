<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\service\json;


final class json_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        bool $useBase64,
        callable $errorFactory,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory
    ): object {
        $arguments = [
            $useBase64,
            $errorFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
        ];

        return $this->serviceFactoryMap->create(
            $className,
            json::class,
            $arguments,
            $arguments
        );
    }

}

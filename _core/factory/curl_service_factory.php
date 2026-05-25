<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\curl as core_curl_service;

final class curl_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        string $url,
        int|float|string $index,
        object $state,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        object $curlAdapter,
        ?callable $arrayAdducer = null,
        ?callable $arrayValueReader = null
    ): mixed {
        $arguments = [
            $url,
            $index,
            $state,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
        ];
        $coreArguments = [
            ...$arguments,
            $curlAdapter,
            $arrayAdducer ?? static fn(mixed $value): array => \adduceToArray($value),
            $arrayValueReader ?? static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default),
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_curl_service::class,
            $coreArguments,
            $arguments
        );
    }

}

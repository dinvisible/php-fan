<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\request as core_request_service;

final class request_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        object $input,
        object $runtime,
        callable $jsonFactory,
        callable $cookieFactory,
        callable $matcherFactory,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        ?callable $arrayAdducer = null,
        ?callable $recursiveMerger = null,
        ?callable $arrayValueReader = null,
        ?callable $classNameResolver = null
    ): object {
        $arguments = [
            $input,
            $runtime,
            $jsonFactory,
            $cookieFactory,
            $matcherFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $arrayAdducer ?? static fn(mixed $value): array => \adduceToArray($value),
            $recursiveMerger ?? static fn(mixed ...$values): mixed => \array_merge_recursive_alt(...$values),
            $arrayValueReader ?? static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default),
            $classNameResolver ?? static fn(object $object): string => \get_class_alt($object) ?? get_class($object)
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_request_service::class,
            $arguments,
            $arguments
        );
    }

}

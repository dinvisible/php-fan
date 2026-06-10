<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\config as core_config_service;

final class config_service_factory
{
    private \Closure $configuredServiceFactory;

    private \Closure $shortClassNameResolver;

    public function __construct(
        callable $configuredServiceFactory,
        ?callable $shortClassNameResolver = null
    )
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
        $this->shortClassNameResolver = \Closure::fromCallable($shortClassNameResolver ?? static fn(object|string $object): string => \get_class_name($object) ?? (is_object($object) ? get_class($object) : $object));
    }

    public function __invoke(
        string $className,
        string $configType,
        string $sourceType,
        callable $configFactory,
        callable $configCacheFactory,
        object $configState,
        object $runtime,
        object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        callable $serviceCacheFactory,
        callable $phpArrayFileLoader,
        callable $configRowFactory,
        object $sourceFileMetadata,
        object $sourceFileStorage,
        ?callable $shortClassNameResolver = null
    ): mixed {
        $arguments = [
            $configType,
            $sourceType,
            $configFactory,
            $configCacheFactory,
            $configState,
            $runtime,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $phpArrayFileLoader,
            $configRowFactory,
            $sourceFileMetadata,
            $sourceFileStorage,
            $shortClassNameResolver ?? $this->shortClassNameResolver
        ];

        if ($className === core_config_service::class) {
            return new core_config_service(...$arguments);
        }

        return ($this->configuredServiceFactory)($className, $arguments);
    }

}

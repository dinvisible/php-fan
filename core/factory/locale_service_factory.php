<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\locale as core_locale_service;

final class locale_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        bool $allowIni,
        callable $entityFactory,
        callable $tabFactory,
        callable $sessionFactory,
        callable $requestFactory,
        callable $cookieFactory,
        callable $matcherFactory,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        ?callable $arrayAdducer = null,
        ?callable $classNameResolver = null
    ): mixed {
        $arguments = [
            $allowIni,
            $entityFactory,
            $tabFactory,
            $sessionFactory,
            $requestFactory,
            $cookieFactory,
            $matcherFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $arrayAdducer ?? static fn(mixed $value): array => \adduceToArray($value),
            $classNameResolver ?? static fn(object $object): string => \get_class_alt($object) ?? get_class($object)
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_locale_service::class,
            $arguments,
            $arguments
        );
    }

}

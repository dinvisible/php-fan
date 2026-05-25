<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\date as core_date_service;

final class date_service_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        \DateTime $date,
        mixed $format,
        bool $isTime,
        mixed $timezone,
        bool $save,
        ?object $state,
        ?callable $dateFactory,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        ?callable $classNameResolver = null,
        ?callable $arrayValueReader = null,
        ?callable $dateInstanceFactory = null
    ): mixed {
        $dateInstanceFactory ??= function (
            \DateTime $date,
            mixed $format,
            bool $isTime,
            mixed $timezone,
            bool $save,
            ?object $state,
            ?callable $dateFactory,
            ?object $serviceBootstrapRuntime,
            ?object $serviceConfigurator,
            ?callable $serviceCacheFactory,
            ?callable $classNameResolver = null,
            ?callable $arrayValueReader = null
        ) use ($className): mixed {
            if ($serviceBootstrapRuntime === null || $serviceConfigurator === null || $serviceCacheFactory === null) {
                throw new \RuntimeException('Date instance factory dependencies are not configured.');
            }

            return $this->__invoke(
                $className,
                $date,
                $format,
                $isTime,
                $timezone,
                $save,
                $state,
                $dateFactory,
                $serviceBootstrapRuntime,
                $serviceConfigurator,
                $serviceCacheFactory,
                $classNameResolver,
                $arrayValueReader
            );
        };

        $arguments = [
            $date,
            $format,
            $isTime,
            $timezone,
            $save,
            $state,
            $dateFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $classNameResolver,
            $arrayValueReader,
            $dateInstanceFactory
        ];

        if ($className === core_date_service::class) {
            return new core_date_service(...$arguments);
        }

        return ($this->configuredServiceFactory)($className, $arguments);
    }

}

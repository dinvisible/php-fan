<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\user as core_user_service;

final class user_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        mixed $identifyer,
        string $userSpace,
        callable $configFactory,
        callable $sessionFactory,
        callable $currentUserFactory,
        callable $applicationFactory,
        callable $errorFactory,
        callable $requestInputFactory,
        callable $entityFactory,
        callable $userEngineFactory,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        object $userState,
        callable $instanceKeyEncoder,
        callable $snapshotEncoder,
        callable $snapshotDecoder,
        ?callable $arrayAdducer = null
    ): object {
        $arguments = [
            $identifyer,
            $userSpace,
            $configFactory,
            $sessionFactory,
            $currentUserFactory,
            $applicationFactory,
            $errorFactory,
            $requestInputFactory,
            $entityFactory,
            $userEngineFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $userState,
            $instanceKeyEncoder,
            $snapshotEncoder,
            $snapshotDecoder,
            $arrayAdducer ?? static fn(mixed $value): array => \adduceToArray($value),
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_user_service::class,
            $arguments,
            $arguments
        );
    }

}

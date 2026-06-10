<?php

declare(strict_types=1);

use fan\core\di\application_infrastructure_service_factory_defaults_provider;
use fan\core\di\cache_service_factory;
use fan\core\di\config_service_factory;
use fan\core\di\file_system_service_factory;
use fan\core\di\json_service_factory;
use PHPUnit\Framework\TestCase;

final class ApplicationInfrastructureServiceFactoryDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesInfrastructureFactoryDefaults(): void
    {
        $defaultsProvider = new application_infrastructure_service_factory_defaults_provider(
            static fn(callable $configuredServiceFactory): callable => new config_service_factory(
                $configuredServiceFactory,
                null,
                new ApplicationInfrastructureServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new file_system_service_factory(
                $configuredServiceFactory,
                new ApplicationInfrastructureServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new json_service_factory(
                $configuredServiceFactory,
                new ApplicationInfrastructureServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new cache_service_factory(
                $configuredServiceFactory,
                new ApplicationInfrastructureServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            )
        );

        $this->assertIsCallable($defaultsProvider->configServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->fileSystemServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->jsonServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->cacheServiceFactory()(self::configuredServiceFactory()));
    }

    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }
}

final class ApplicationInfrastructureServiceFactoryDefaultsProviderReflectionClassFactoryDouble
{
    public function create(object|string $object): ReflectionClass
    {
        return new ReflectionClass($object);
    }
}

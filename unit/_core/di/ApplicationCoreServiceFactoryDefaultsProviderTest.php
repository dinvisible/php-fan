<?php

declare(strict_types=1);

use fan\core\di\application_core_service_factory_defaults_provider;
use fan\core\di\application_service_factory;
use fan\core\di\debug_service_factory;
use fan\core\di\error_service_factory;
use fan\core\di\header_service_factory;
use fan\core\di\locale_service_factory;
use fan\core\di\matcher_service_factory;
use fan\core\di\plain_service_factory;
use fan\core\di\reflector_service_factory;
use fan\core\di\request_service_factory;
use fan\core\di\role_service_factory;
use fan\core\di\timer_program_factory;
use fan\core\di\timer_service_factory;
use PHPUnit\Framework\TestCase;

final class ApplicationCoreServiceFactoryDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesCoreFactoryDefaults(): void
    {
        $defaultsProvider = new application_core_service_factory_defaults_provider(
            static fn(callable $configuredServiceFactory): callable => new request_service_factory(
                $configuredServiceFactory,
                new ApplicationCoreServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new role_service_factory(
                $configuredServiceFactory,
                new ApplicationCoreServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new error_service_factory(
                $configuredServiceFactory,
                new ApplicationCoreServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new reflector_service_factory($configuredServiceFactory),
            static fn(callable $configuredServiceFactory): callable => new application_service_factory(
                $configuredServiceFactory,
                new ApplicationCoreServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new debug_service_factory($configuredServiceFactory),
            static fn(callable $configuredServiceFactory): callable => new header_service_factory(
                $configuredServiceFactory,
                null,
                new ApplicationCoreServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new matcher_service_factory(
                $configuredServiceFactory,
                new ApplicationCoreServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new timer_service_factory(
                $configuredServiceFactory,
                new ApplicationCoreServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new plain_service_factory(
                $configuredServiceFactory,
                new ApplicationCoreServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new locale_service_factory(
                $configuredServiceFactory,
                new ApplicationCoreServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new timer_program_factory($configuredServiceFactory)
        );

        $this->assertIsCallable($defaultsProvider->requestServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->roleServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->errorServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->reflectorServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->applicationServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->debugServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->headerServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->matcherServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->timerServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->plainServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->localeServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->timerProgramFactory()(self::configuredServiceFactory()));
    }

    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }
}

final class ApplicationCoreServiceFactoryDefaultsProviderReflectionClassFactoryDouble
{
    public function create(object|string $object): ReflectionClass
    {
        return new ReflectionClass($object);
    }
}

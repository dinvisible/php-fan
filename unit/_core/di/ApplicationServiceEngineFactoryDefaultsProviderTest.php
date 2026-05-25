<?php

declare(strict_types=1);

use fan\core\di\application_service_engine_factory_defaults_provider;
use fan\core\di\cache_engine_factory;
use fan\core\di\service_engine_factory;
use fan\core\di\session_engine_factory;
use fan\core\di\user_engine_factory;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\reflection_class_factory;


final class ApplicationServiceEngineFactoryDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesEngineFactoryDefaults(): void
    {
        $defaultsProvider = self::defaultsProvider();

        $this->assertIsCallable($defaultsProvider->bootstrapRuntimeServiceFactory());
        $this->assertIsCallable($defaultsProvider->serviceEngineFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->cacheEngineFactory()(new stdClass(), self::configuredServiceFactory(), new stdClass()));
        $this->assertIsCallable($defaultsProvider->sessionEngineFactory()(self::configuredServiceFactory(), new stdClass()));
        $this->assertIsCallable($defaultsProvider->userEngineFactory()(new stdClass(), self::configuredServiceFactory()));
        $this->assertFalse(method_exists($defaultsProvider, 'emailEngineFactory'));
        $this->assertFalse(method_exists($defaultsProvider, 'databaseEngineFactory'));
    }

    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }

    private static function defaultsProvider(): application_service_engine_factory_defaults_provider
    {
        return new application_service_engine_factory_defaults_provider(
            static fn(): callable => static fn(): object => new stdClass(),
            static fn(callable $configuredServiceFactory): callable => new service_engine_factory($configuredServiceFactory),
            static fn(
                object $serializerOperations,
                callable $configuredServiceFactory,
                object $cacheFileStorage
            ): callable => new cache_engine_factory(
                $serializerOperations,
                $configuredServiceFactory,
                $cacheFileStorage,
                null,
                null,
                new reflection_class_factory()
            ),
            static fn(callable $configuredServiceFactory, object $nativeSession): callable => new session_engine_factory(
                $configuredServiceFactory,
                $nativeSession,
                null,
                new ApplicationServiceEngineFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(object $serializerOperations, callable $configuredServiceFactory): callable => new user_engine_factory(
                $serializerOperations,
                $configuredServiceFactory
            )
        );
    }
}

final class ApplicationServiceEngineFactoryDefaultsProviderReflectionClassFactoryDouble
{
    public function create(object|string $object): ReflectionClass
    {
        return new ReflectionClass($object);
    }
}

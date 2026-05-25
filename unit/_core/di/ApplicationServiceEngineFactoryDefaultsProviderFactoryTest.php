<?php

declare(strict_types=1);

use fan\core\di\application_service_engine_factory_defaults_provider;
use fan\core\di\application_service_engine_factory_defaults_provider_factory;
use PHPUnit\Framework\TestCase;
use fan\core\di\application_registry_defaults_provider_factory;

final class ApplicationServiceEngineFactoryDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationServiceEngineFactoryDefaultsProvider(): void
    {
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();
        $provider = (new application_service_engine_factory_defaults_provider_factory())($adapterRegistry);

        $this->assertInstanceOf(application_service_engine_factory_defaults_provider::class, $provider);
        $this->assertIsCallable($provider->bootstrapRuntimeServiceFactory());
        $this->assertIsCallable($provider->serviceEngineFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($provider->cacheEngineFactory()(new stdClass(), self::configuredServiceFactory(), new stdClass()));
        $this->assertIsCallable($provider->sessionEngineFactory()(self::configuredServiceFactory(), new stdClass()));
        $this->assertIsCallable($provider->userEngineFactory()(new stdClass(), self::configuredServiceFactory()));
        $this->assertFalse(method_exists($provider, 'emailEngineFactory'));
        $this->assertFalse(method_exists($provider, 'databaseEngineFactory'));
    }

    public function testFactoryAcceptsInjectedEngineFactoryDefaults(): void
    {
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();
        $provider = (new application_service_engine_factory_defaults_provider_factory(
            static fn(): callable => static fn(): object => (object)['name' => 'bootstrap-runtime'],
            static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'service-engine'],
            static fn(
                object $serializerOperations,
                callable $configuredServiceFactory,
                object $cacheFileStorage
            ): callable => static fn(): object => (object)['name' => 'cache-engine'],
            function ($registry) use ($adapterRegistry): callable {
                $this->assertSame($adapterRegistry, $registry);

                return static fn(callable $configuredServiceFactory, object $nativeSession): callable => static fn(): object => (object)['name' => 'session-engine'];
            },
            static fn(object $serializerOperations, callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'user-engine']
        ))($adapterRegistry);

        $this->assertSame('bootstrap-runtime', $provider->bootstrapRuntimeServiceFactory()()()->name);
        $this->assertSame('service-engine', $provider->serviceEngineFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('cache-engine', $provider->cacheEngineFactory()(new stdClass(), self::configuredServiceFactory(), new stdClass())()->name);
        $this->assertSame('session-engine', $provider->sessionEngineFactory()(self::configuredServiceFactory(), new stdClass())()->name);
        $this->assertSame('user-engine', $provider->userEngineFactory()(new stdClass(), self::configuredServiceFactory())()->name);
    }
    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }
}

<?php

declare(strict_types=1);

use fan\core\di\application_adapter_registry;
use fan\core\di\application_adapter_registry_defaults_provider;
use fan\core\di\application_deferred_service_factory_provider;
use fan\core\di\application_model_factory_provider;
use fan\core\di\service_factory_registry;
use PHPUnit\Framework\TestCase;
use fan\core\di\application_registry_defaults_provider_factory;


final class ApplicationDeferredServiceFactoryProviderTest extends TestCase
{
    public function testProviderOwnsDeferredFactoryDefaults(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_deferred_service_factory_provider.php');
        $containerSource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_container_factory.php');
        $bundleSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_container_dependency_bundle.php');

        $this->assertIsString($source);
        $this->assertIsString($containerSource);
        $this->assertIsString($bundleSource);
        $this->assertStringContainsString('final class application_deferred_service_factory_provider', $source);
        $this->assertStringContainsString("\$serviceFactoryRegistry->get('cacheEngineFactory')", $source);
        $this->assertStringContainsString('$adapterRegistry->cacheFileStorage()', $source);
        $this->assertStringContainsString("\$serviceFactoryRegistry->get('sessionEngineFactory')", $source);
        $this->assertStringContainsString('$adapterRegistry->nativeSession()', $source);
        $this->assertStringContainsString('$modelFactoryProvider->modelRowFactory(', $source);
        $this->assertStringContainsString('$modelFactoryProvider->modelRowsetFactory(', $source);
        $this->assertStringContainsString('public application_deferred_service_factory_provider $deferredServiceFactoryProvider', $bundleSource);
        $this->assertStringContainsString('$deferredServiceFactoryProvider = $dependencyBundle->deferredServiceFactoryProvider;', $containerSource);
        $this->assertStringContainsString('$deferredServiceFactoryProvider->cacheEngineFactory(', $containerSource);
        $this->assertStringNotContainsString('$serviceFactoryRegistry->cacheEngineFactory($serializerOperations', $containerSource);
        $this->assertStringNotContainsString('$modelFactoryProvider->modelRowFactory($serializerOperations', $containerSource);
    }

    public function testProviderKeepsInjectedOverrides(): void
    {
        $provider = new application_deferred_service_factory_provider();
        $override = static fn(): object => (object)['name' => 'override'];
        $serializerOperations = (object)['name' => 'serializer'];
        $configuredServiceFactory = static fn(): object => (object)['name' => 'configured'];
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();
        $serviceFactoryRegistry = self::serviceFactoryRegistry();
        $modelFactoryProvider = self::modelFactoryProvider();

        $this->assertSame($override, $provider->cacheEngineFactory($override, $serializerOperations, $configuredServiceFactory, $adapterRegistry, $serviceFactoryRegistry));
        $this->assertSame($override, $provider->sessionEngineFactory($override, $configuredServiceFactory, $adapterRegistry, $serviceFactoryRegistry));
        $this->assertSame($override, $provider->userEngineFactory($override, $serializerOperations, $configuredServiceFactory, $serviceFactoryRegistry));
        $this->assertSame($override, $provider->modelRowFactory($override, $serializerOperations, $configuredServiceFactory, $modelFactoryProvider));
        $this->assertSame($override, $provider->modelRowsetFactory($override, $serializerOperations, $configuredServiceFactory, $modelFactoryProvider));
    }

    public function testProviderBuildsCallableDefaults(): void
    {
        $provider = new application_deferred_service_factory_provider();
        $serializerOperations = (object)['name' => 'serializer'];
        $configuredServiceFactory = static fn(): object => (object)['name' => 'configured'];
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();
        $serviceFactoryRegistry = self::serviceFactoryRegistry();
        $modelFactoryProvider = self::modelFactoryProvider();

        $this->assertIsCallable($provider->cacheEngineFactory(null, $serializerOperations, $configuredServiceFactory, $adapterRegistry, $serviceFactoryRegistry));
        $this->assertIsCallable($provider->sessionEngineFactory(null, $configuredServiceFactory, $adapterRegistry, $serviceFactoryRegistry));
        $this->assertIsCallable($provider->userEngineFactory(null, $serializerOperations, $configuredServiceFactory, $serviceFactoryRegistry));
        $this->assertIsCallable($provider->modelRowFactory(null, $serializerOperations, $configuredServiceFactory, $modelFactoryProvider));
        $this->assertIsCallable($provider->modelRowsetFactory(null, $serializerOperations, $configuredServiceFactory, $modelFactoryProvider));
    }

    private static function modelFactoryProvider(): application_model_factory_provider
    {
        return new application_model_factory_provider(
            static fn(callable $configuredServiceFactory): callable => static fn(): object => new stdClass(),
            static fn(callable $configuredServiceFactory): callable => static fn(): object => new stdClass(),
            static fn(callable $configuredServiceFactory): callable => static fn(): object => new stdClass(),
            static fn(object $serializerOperations, callable $configuredServiceFactory): callable => static fn(): object => new stdClass(),
            static fn(object $serializerOperations, callable $configuredServiceFactory): callable => static fn(): object => new stdClass(),
            static fn(callable $configuredServiceFactory): callable => static fn(): object => new stdClass()
        );
    }

    private static function serviceFactoryRegistry(): service_factory_registry
    {
        $configuredFactory = static fn(callable $configuredServiceFactory): callable => static fn(): object => new stdClass();
        $factories = array_fill(0, 40, $configuredFactory);
        $factories[0] = static fn(): callable => static fn(): object => new stdClass();
        $factories[30] = static fn(): callable => static fn(): object => new stdClass();
        $factories[37] = static fn(object $serializerOperations, callable $configuredServiceFactory, object $cacheFileStorage): callable => static fn(): object => new stdClass();
        $factories[38] = static fn(callable $configuredServiceFactory, object $nativeSession): callable => static fn(): object => new stdClass();
        $factories[39] = static fn(object $serializerOperations, callable $configuredServiceFactory): callable => static fn(): object => new stdClass();

        return new service_factory_registry(array_combine(self::serviceFactoryNames(), $factories));
    }

    /**
     * @return list<string>
     */
    private static function serviceFactoryNames(): array
    {
        return [
            'bootstrapRuntimeServiceFactory',
            'serviceEngineFactory',
            'configServiceFactory',
            'sessionServiceFactory',
            'userServiceFactory',
            'requestServiceFactory',
            'roleServiceFactory',
            'tabServiceFactory',
            'errorServiceFactory',
            'reflectorServiceFactory',
            'applicationServiceFactory',
            'debugServiceFactory',
            'headerServiceFactory',
            'matcherServiceFactory',
            'timerServiceFactory',
            'plainServiceFactory',
            'localeServiceFactory',
            'timerProgramFactory',
            'curlServiceFactory',
            'restServiceFactory',
            'cookieServiceFactory',
            'pagerServiceFactory',
            'obfuscatorServiceFactory',
            'imageModifyServiceFactory',
            'soapServiceFactory',
            'dateServiceFactory',
            'fileSystemServiceFactory',
            'jsonServiceFactory',
            'cacheServiceFactory',
            'translationServiceFactory',
            'matcherItemFactory',
            'matcherItemComponentFactory',
            'tabDelegateFactory',
            'tabViewParserFactory',
            'plainControllerFactory',
            'blockFactory',
            'blockExceptionFactory',
            'cacheEngineFactory',
            'sessionEngineFactory',
            'userEngineFactory',
        ];
    }
}

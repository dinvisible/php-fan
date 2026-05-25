<?php

declare(strict_types=1);

use fan\core\di\application_model_factory_provider;
use fan\core\di\application_runtime_factory_provider;
use fan\core\di\application_service_factory_bundle;
use fan\core\di\application_service_factory_options;
use fan\core\di\service_factory_registry;
use PHPUnit\Framework\TestCase;

final class ApplicationServiceFactoryBundleTest extends TestCase
{
    public function testBundleBuildsCallableDefaultsFromProviders(): void
    {
        $bundle = application_service_factory_bundle::fromProviders(
            self::runtimeFactoryProvider(),
            self::modelFactoryProvider(),
            self::serviceFactoryRegistry(),
            new application_service_factory_options()
        );

        foreach ($this->defaultClosureProperties() as $propertyName) {
            $this->assertInstanceOf(\Closure::class, $bundle->{$propertyName}, $propertyName);
        }
        foreach ([
            'cacheEngineFactory',
            'sessionEngineFactory',
            'userEngineFactory',
            'modelRowFactory',
            'modelRowsetFactory',
        ] as $propertyName) {
            $this->assertNull($bundle->{$propertyName}, $propertyName);
        }
    }

    public function testBundleKeepsInjectedFactoryOverrides(): void
    {
        $requestInputFactory = static fn(): object => (object)['name' => 'request'];
        $cacheEngineFactory = static fn(): object => (object)['name' => 'cache-engine'];
        $configuredServiceFactory = static fn(): object => (object)['name' => 'configured'];

        $bundle = application_service_factory_bundle::fromProviders(
            self::runtimeFactoryProvider(),
            self::modelFactoryProvider(),
            self::serviceFactoryRegistry(),
            new application_service_factory_options(
                requestInputFactory: $requestInputFactory,
                cacheEngineFactory: $cacheEngineFactory,
                configuredServiceFactory: $configuredServiceFactory
            )
        );

        $this->assertSame($requestInputFactory, $bundle->requestInputFactory);
        $this->assertSame($cacheEngineFactory, $bundle->cacheEngineFactory);
        $this->assertSame($configuredServiceFactory, $bundle->configuredServiceFactory);
    }

    public function testSourceMovesFactoryClosurePropertiesOutOfContainerFactory(): void
    {
        $bundleSource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_service_factory_bundle.php');
        $containerSource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_container_factory.php');

        $this->assertIsString($bundleSource);
        $this->assertIsString($containerSource);
        $this->assertStringContainsString('final class application_service_factory_bundle', $bundleSource);
        $this->assertStringContainsString('application_service_factory_options $factoryOptions', $bundleSource);
        $this->assertStringContainsString('private application_service_factory_bundle $factoryBundle;', $containerSource);
        $this->assertStringContainsString('application_service_factory_bundle $factoryBundle,', $containerSource);
        $this->assertStringNotContainsString('application_service_factory_bundle::fromProviders(', $containerSource);
        $this->assertStringNotContainsString('application_service_factory_options $factoryOptions,', $containerSource);
        $this->assertStringNotContainsString('new application_service_factory_options()', $containerSource);
        $this->assertStringNotContainsString('private \Closure $requestInputFactory;', $containerSource);
        $this->assertStringNotContainsString('private ?\Closure $cacheEngineFactory;', $containerSource);
    }

    /**
     * @return list<string>
     */
    private function defaultClosureProperties(): array
    {
        return [
            'requestInputFactory',
            'bootstrapOperationsFactory',
            'phpArrayFileLoader',
            'serializerOperationsFactory',
            'configServiceFactory',
            'bootstrapRuntimeServiceFactory',
            'serviceEngineFactory',
            'sessionServiceFactory',
            'userServiceFactory',
            'timerProgramFactory',
            'timerServiceFactory',
            'plainControllerFactory',
            'plainServiceFactory',
            'localeServiceFactory',
            'matcherItemFactory',
            'matcherItemComponentFactory',
            'tabDelegateFactory',
            'tabViewParserFactory',
            'entityDesignerFactory',
            'entityEncapsulantFactory',
            'curlServiceFactory',
            'restServiceFactory',
            'cookieServiceFactory',
            'pagerServiceFactory',
            'obfuscatorServiceFactory',
            'imageModifyServiceFactory',
            'soapServiceFactory',
            'dateServiceFactory',
            'modelEntityFactory',
            'modelRequestFactory',
            'fileSystemServiceFactory',
            'jsonServiceFactory',
            'cacheServiceFactory',
            'blockFactory',
            'blockExceptionFactory',
            'translationServiceFactory',
            'requestServiceFactory',
            'roleServiceFactory',
            'tabServiceFactory',
            'errorServiceFactory',
            'reflectorServiceFactory',
            'applicationServiceFactory',
            'debugServiceFactory',
            'headerServiceFactory',
            'matcherServiceFactory',
            'configuredServiceFactory',
        ];
    }

    private static function runtimeFactoryProvider(): application_runtime_factory_provider
    {
        return new application_runtime_factory_provider(
            static fn(string $className, array $arguments): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(string $path, mixed $default = null): mixed => $default,
            static fn(object $warningCapture): object => new stdClass()
        );
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

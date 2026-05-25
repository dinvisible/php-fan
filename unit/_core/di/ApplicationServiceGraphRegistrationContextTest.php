<?php

declare(strict_types=1);

use fan\core\di\application_container_dependency_bundle;
use fan\core\di\application_container_dependency_provider;
use fan\core\di\application_model_factory_provider;
use fan\core\di\application_runtime_factory_provider;
use fan\core\di\application_service_factory_bundle;
use fan\core\di\application_service_factory_options;
use fan\core\di\service_factory_registry;
use fan\core\di\application_service_graph_registration_context;
use PHPUnit\Framework\TestCase;
use fan\core\di\application_adapter_registry;
use fan\core\di\application_factory_provider_defaults_provider;
use fan\core\di\application_factory_provider_defaults_provider_factory;
use fan\core\di\application_registry_defaults_provider;
use fan\core\di\application_registry_defaults_provider_factory;
use fan\core\di\application_service_creator_defaults_provider;
use fan\core\di\application_service_creator_defaults_provider_factory;
use fan\core\di\application_service_registrar_defaults_provider;
use fan\core\di\application_service_registrar_defaults_provider_factory;


final class ApplicationServiceGraphRegistrationContextTest extends TestCase
{
    public function testContextBuildsTypedRegistrationDependenciesFromBundles(): void
    {
        $dependencyBundle = application_container_dependency_bundle::fromProvider(self::dependencyProvider());
        $factoryBundle = application_service_factory_bundle::fromProviders(
            new application_runtime_factory_provider(
                static fn(string $className, array $arguments): object => new stdClass(),
                static fn(): object => new stdClass(),
                static fn(string $path, mixed $default = null): mixed => $default,
                static fn(object $warningCapture): object => new stdClass()
            ),
            self::modelFactoryProvider(),
            self::serviceFactoryRegistry(),
            new application_service_factory_options()
        );
        $cacheEngineFactory = static fn(): object => (object)['name' => 'cache-engine'];
        $sessionEngineFactory = static fn(): object => (object)['name' => 'session-engine'];
        $userEngineFactory = static fn(): object => (object)['name' => 'user-engine'];
        $modelRowFactory = static fn(): object => (object)['name' => 'model-row'];
        $modelRowsetFactory = static fn(): object => (object)['name' => 'model-rowset'];
        $context = application_service_graph_registration_context::fromBundles(
            $dependencyBundle,
            $factoryBundle,
            $cacheEngineFactory,
            $sessionEngineFactory,
            $userEngineFactory,
            $modelRowFactory,
            $modelRowsetFactory
        );

        $this->assertSame($dependencyBundle->coreServiceCreator, $context->coreServiceCreator);
        $this->assertSame($dependencyBundle->userServiceCreator, $context->userServiceCreator);
        $this->assertSame($factoryBundle->configServiceFactory, $context->configServiceFactory);
        $this->assertSame($factoryBundle->requestServiceFactory, $context->requestServiceFactory);
        $this->assertInstanceOf(\Closure::class, $context->cacheEngineFactory);
        $this->assertInstanceOf(\Closure::class, $context->sessionEngineFactory);
        $this->assertInstanceOf(\Closure::class, $context->userEngineFactory);
        $this->assertInstanceOf(\Closure::class, $context->modelRowFactory);
        $this->assertInstanceOf(\Closure::class, $context->modelRowsetFactory);
    }

    public function testSourceMovesRegistrationArgumentSurfaceOutOfContainerFactory(): void
    {
        $contextSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_service_graph_registration_context.php');
        $registrarSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_service_graph_registrar.php');
        $containerSource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_container_factory.php');

        $this->assertIsString($contextSource);
        $this->assertIsString($registrarSource);
        $this->assertIsString($containerSource);
        $this->assertStringContainsString('final class application_service_graph_registration_context', $contextSource);
        $this->assertStringContainsString('application_container_dependency_bundle $dependencyBundle', $contextSource);
        $this->assertStringContainsString('application_service_factory_bundle $factoryBundle', $contextSource);
        $this->assertStringContainsString('application_service_graph_registration_context $context', $registrarSource);
        $this->assertStringContainsString('application_service_graph_registration_context::fromBundles(', $containerSource);
        $this->assertStringNotContainsString('callable $configServiceFactory,', $registrarSource);
        $this->assertStringNotContainsString('$coreServiceCreator = $dependencyBundle->coreServiceCreator;', $containerSource);
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

    private static function dependencyProvider(): application_container_dependency_provider
    {
        return new application_container_dependency_provider(
            static fn(): application_registry_defaults_provider => (new application_registry_defaults_provider_factory())(),
            static fn(
                application_adapter_registry $adapterRegistry
            ): application_factory_provider_defaults_provider => (new application_factory_provider_defaults_provider_factory())($adapterRegistry),
            static fn(): application_service_registrar_defaults_provider => (new application_service_registrar_defaults_provider_factory())(),
            static fn(): application_service_creator_defaults_provider => (new application_service_creator_defaults_provider_factory())()
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

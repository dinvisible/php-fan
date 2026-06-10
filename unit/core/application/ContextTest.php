<?php

declare(strict_types=1);

use fan\core\bootstrap\context;
use fan\core\bootstrap\state;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;
use fan\core\di\context_defaults_factory;


final class BootstrapContextTest extends TestCase
{
    public function testContextReusesInjectedState(): void
    {
        $state = new state();
        $context = new context($state, defaultFactoriesFactory: self::defaultFactoriesFactory());

        $this->assertSame($state, $context->state());
    }

    public function testContextCreatesContainerThroughInjectedFactoryAndStoresItInState(): void
    {
        $calls = 0;
        $container = new container();
        $context = new context(
            containerFactory: function () use (&$calls, $container): container {
                $calls++;
                return $container;
            },
            defaultFactoriesFactory: self::defaultFactoriesFactory()
        );

        $this->assertSame($container, $context->container());
        $this->assertSame($container, $context->container());
        $this->assertSame($container, $context->state()->container());
        $this->assertSame(1, $calls);
    }

    public function testDefaultContainerFactoryDoesNotUseStaticRegistry(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/application/context.php');
        $containerFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/context_container_factory.php');
        $defaultsFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/context_defaults_factory.php');
        $coreDefaultsFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/context_core_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($containerFactorySource);
        $this->assertIsString($defaultsFactorySource);
        $this->assertIsString($coreDefaultsFactorySource);
        $this->assertStringNotContainsString('new context_defaults_factory()', $source);
        $this->assertStringContainsString('Bootstrap context default factories factory is not configured.', $source);
        $this->assertStringNotContainsString('new state()', $source);
        $this->assertStringNotContainsString('new context_container_factory()', $source);
        $this->assertStringContainsString('$contextCoreDefaults = (new context_core_defaults_provider_factory())();', $defaultsFactorySource);
        $this->assertStringContainsString('$coreDefaults = $contextCoreDefaults();', $defaultsFactorySource);
        $this->assertStringNotContainsString('$coreDefaultsProvider = (new context_core_defaults_provider_factory())();', $defaultsFactorySource);
        $this->assertStringNotContainsString('$coreDefaults = (new context_core_defaults_factory())();', $defaultsFactorySource);
        $this->assertStringContainsString('$contextStateDefaults = new bootstrap_state_defaults_factory();', $coreDefaultsFactorySource);
        $this->assertStringContainsString("'stateFactory' => \$contextStateDefaults->stateFactory()", $coreDefaultsFactorySource);
        $this->assertStringNotContainsString('$bootstrapStateDefaults = new bootstrap_state_defaults_factory();', $coreDefaultsFactorySource);
        $this->assertStringNotContainsString("'stateFactory' => static fn(): state => new state()", $defaultsFactorySource);
        $this->assertStringNotContainsString('new context_state_factory()', $defaultsFactorySource);
        $this->assertStringContainsString('$contextContainerDefaults = new context_container_defaults_factory(', $coreDefaultsFactorySource);
        $this->assertStringContainsString("'containerFactory' => \$contextContainerDefaults()", $coreDefaultsFactorySource);
        $this->assertStringNotContainsString('$containerFactory = (new context_container_defaults_factory(', $coreDefaultsFactorySource);
        $this->assertStringContainsString('static fn(callable $applicationContainerFactory): context_container_factory => new context_container_factory(', $coreDefaultsFactorySource);
        $this->assertStringNotContainsString('$applicationContainerDefaults = (new application_container_defaults_provider_factory())();', $coreDefaultsFactorySource);
        $this->assertStringNotContainsString('$applicationContainerDefaults->containerFactory()', $coreDefaultsFactorySource);
        $this->assertStringNotContainsString('application_container_defaults_factory::containerFactory()', $coreDefaultsFactorySource);
        $this->assertStringNotContainsString('$applicationContainerFactory = $this->applicationContainerFactory();', $defaultsFactorySource);
        $this->assertStringNotContainsString('$containerRegistryStateFactory = new container_registry_state_factory($applicationContainerFactory);', $defaultsFactorySource);
        $this->assertStringNotContainsString('$containerRegistryStateSetter = new container_registry_state_setter();', $defaultsFactorySource);
        $this->assertStringNotContainsString("new context_container_factory(", $defaultsFactorySource);
        $this->assertStringContainsString("'containerFactory' => \$contextContainerDefaults()", $coreDefaultsFactorySource);
        $this->assertStringNotContainsString('$applicationContainerFactory', $defaultsFactorySource);
        $this->assertStringNotContainsString("\$containerRegistryStateFactory", $defaultsFactorySource);
        $this->assertStringNotContainsString("\$containerRegistryStateSetter", $defaultsFactorySource);
        $this->assertStringNotContainsString('new application_container_factory()', $source);
        $this->assertStringNotContainsString('new application_container_factory()', $containerFactorySource);
        $this->assertStringNotContainsString('container_registry::setState', $containerFactorySource);
        $this->assertStringNotContainsString('\fan\core\di\container_registry::setState($state);', $defaultsFactorySource);
        $this->assertStringNotContainsString('new application_service_factory_options(', $defaultsFactorySource);
        $this->assertStringNotContainsString('application_container_dependency_bundle::fromProvider(', $defaultsFactorySource);
        $this->assertStringNotContainsString('new \fan\core\di\application_container_dependency_provider()', $defaultsFactorySource);
        $this->assertStringNotContainsString('application_service_factory_bundle::fromProviders(', $defaultsFactorySource);
        $this->assertStringNotContainsString('new \fan\core\di\application_container_factory($factoryBundle, $dependencyBundle)', $defaultsFactorySource);
        $this->assertStringNotContainsString('container_registry::createDefaultContainer()', $source);
        $this->assertStringNotContainsString('new request_input_factory()', $source);
        $this->assertStringNotContainsString('new bootstrap_runtime_factory()', $source);
        $this->assertStringNotContainsString('new zend_autoloader_loader_factory()', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\bootstrap_loader_file_storage()', $source);
        $this->assertStringNotContainsString('new bootstrap_object_factory()', $source);
        $this->assertStringNotContainsString('new error_handler_setup()', $source);
        $this->assertStringContainsString('$contextRequestInputDefaults = new bootstrap_request_input_defaults_factory();', $coreDefaultsFactorySource);
        $this->assertStringContainsString("'requestInputFactory' => \$contextRequestInputDefaults->requestInputFactory()", $coreDefaultsFactorySource);
        $this->assertStringNotContainsString("context_core_request_input_defaults_provider_factory", $coreDefaultsFactorySource);
        $this->assertStringNotContainsString("'requestInputFactory' => bootstrap_request_input_defaults_factory::requestInputFactory()", $coreDefaultsFactorySource);
        $this->assertStringNotContainsString("'requestInputFactory' => \$this->requestInputFactory()", $defaultsFactorySource);
        $this->assertStringNotContainsString('new request_input_defaults_factory(', $defaultsFactorySource);
        $this->assertStringContainsString('$contextRuntimeDefaults = new bootstrap_runtime_defaults_factory();', $coreDefaultsFactorySource);
        $this->assertStringContainsString("'bootstrapRuntimeFactory' => \$contextRuntimeDefaults()", $coreDefaultsFactorySource);
        $this->assertStringNotContainsString('$bootstrapRuntimeFactory = (new bootstrap_runtime_defaults_factory()', $coreDefaultsFactorySource);
        $this->assertStringNotContainsString('): bootstrap_runtime_factory => new bootstrap_runtime_factory(', $coreDefaultsFactorySource);
        $this->assertStringNotContainsString('$bootstrapOperationsDefaults = (new bootstrap_operations_defaults_provider_factory())();', $coreDefaultsFactorySource);
        $this->assertStringNotContainsString('static fn(context $context): callable => $bootstrapOperationsDefaults->operationsFactory($context)', $coreDefaultsFactorySource);
        $this->assertStringNotContainsString('static fn(context $context): callable => (new bootstrap_operations_defaults_factory(', $coreDefaultsFactorySource);
        $this->assertStringNotContainsString('static fn(object $operations): bootstrap_operations_factory => new bootstrap_operations_factory($operations)', $coreDefaultsFactorySource);
        $this->assertStringNotContainsString('static fn(context $context): context_bootstrap_operations => new context_bootstrap_operations($context)', $coreDefaultsFactorySource);
        $this->assertStringContainsString("'bootstrapRuntimeFactory' => \$contextRuntimeDefaults()", $coreDefaultsFactorySource);
        $this->assertStringNotContainsString("'bootstrapRuntimeServiceFactory' => \$this->bootstrapRuntimeServiceFactory()", $defaultsFactorySource);
        $this->assertStringNotContainsString("'bootstrapRuntimeStateFactory' => static fn(): array => [", $defaultsFactorySource);
        $this->assertStringNotContainsString('new bootstrap_runtime_defaults_provider_factory()', $coreDefaultsFactorySource);
        $this->assertStringNotContainsString('new \fan\core\di\bootstrap_runtime_service_factory($this->bootstrapRuntimeConstructor())', $defaultsFactorySource);
        $this->assertStringNotContainsString('new bootstrap_runtime_factory(', $defaultsFactorySource);
        $this->assertStringNotContainsString('new bootstrap_runtime_factory()', $defaultsFactorySource);
        $this->assertStringNotContainsString('$this->contextBootstrapOperationsFactory($context)', $defaultsFactorySource);
        $this->assertStringNotContainsString('new bootstrap_operations_factory(new context_bootstrap_operations($context))', $defaultsFactorySource);
        $this->assertStringNotContainsString("\$bootstrapRuntimeDefaults['bootstrapOperationsFactory']", $defaultsFactorySource);
        $this->assertStringNotContainsString("\$bootstrapRuntimeDefaults['bootstrapRuntimeServiceFactory']", $defaultsFactorySource);
        $this->assertStringContainsString('$contextSupportDefaults = (new context_support_defaults_provider_factory())();', $defaultsFactorySource);
        $this->assertStringContainsString('$supportDefaults = $contextSupportDefaults();', $defaultsFactorySource);
        $this->assertStringNotContainsString('$supportDefaultsProvider = (new context_support_defaults_provider_factory())();', $defaultsFactorySource);
        $this->assertStringNotContainsString('$supportDefaults = (new context_support_defaults_factory())();', $defaultsFactorySource);
        $this->assertStringNotContainsString("'zendAutoloaderLoaderFactory' => static fn(): object => new \\fan\\core\\adapter\\zend_autoloader_loader()", $defaultsFactorySource);
        $this->assertStringNotContainsString('new zend_autoloader_loader_factory()', $defaultsFactorySource);
        $this->assertStringNotContainsString('$bootstrapLoaderFileStorage = new \fan\core\adapter\bootstrap_loader_file_storage();', $defaultsFactorySource);
        $this->assertStringNotContainsString("'bootstrapLoaderFileStorageFactory' => static fn(): object => \$bootstrapLoaderFileStorage", $defaultsFactorySource);
        $this->assertStringNotContainsString('new \fan\core\di\configured_service_factory(new \fan\core\di\configured_class_instantiator())', $defaultsFactorySource);
        $this->assertStringNotContainsString('new bootstrap_object_factory($configuredServiceFactory)', $defaultsFactorySource);
        $this->assertStringContainsString('$contextErrorHandlingDefaults = (new context_error_handling_defaults_provider_factory())();', $defaultsFactorySource);
        $this->assertStringContainsString('$errorHandlingDefaults = $contextErrorHandlingDefaults();', $defaultsFactorySource);
        $this->assertStringNotContainsString('$errorHandlingDefaultsProvider = (new context_error_handling_defaults_provider_factory())();', $defaultsFactorySource);
        $this->assertStringNotContainsString('$errorHandlingDefaults = (new error_handling_defaults_factory(', $defaultsFactorySource);
        $this->assertStringNotContainsString('static fn(): bootstrap_error_handler_setup => new bootstrap_error_handler_setup()', $defaultsFactorySource);
        $this->assertStringNotContainsString('static fn(): callable => (new error_logger_defaults_provider_factory())()->errorLogger()', $defaultsFactorySource);
        $this->assertStringNotContainsString('static fn(): callable => error_logger_defaults_factory::errorLogger()', $defaultsFactorySource);
        $this->assertStringNotContainsString('new error_handler_setup($phpRuntimeSettings, $errorHandlerRegistrar)', $defaultsFactorySource);
        $this->assertStringNotContainsString('new \fan\core\service\request_input()', $source);
        $this->assertStringNotContainsString('new \fan\core\service\bootstrap_runtime()', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\zend_autoloader_loader()', $source);
        $this->assertStringNotContainsString('new $class(...$arguments)', $source);
        $this->assertStringNotContainsString('require_once dirname(__DIR__) . \'/service/request_input.php\';', $source);
    }

    public function testContextCreatesBoundaryObjectsThroughInjectedFactories(): void
    {
        $input = new stdClass();
        $runtime = new stdClass();
        $loadedPhpArrays = [];
        $loadedBootstrapConfigs = [];
        $loadedBootstrapErrorHandlers = [];
        $createdObjects = [];
        $errorHandlerCalls = [];
        $errorLogCalls = [];
        $phpRuntimeSettings = new stdClass();
        $bootstrapLoaderFileStorage = new stdClass();
        $registeredHandlers = [];
        $zendLoader = new class {
            public function load(string $zendPath): void
            {
            }
        };
        $context = new context(
            requestInputFactory: static fn(): object => $input,
            bootstrapRuntimeFactory: static fn(): object => $runtime,
            zendAutoloaderLoaderFactory: static fn(): object => $zendLoader,
            bootstrapLoaderFileStorageFactory: static fn(): object => $bootstrapLoaderFileStorage,
            bootstrapObjectFactory: static function (string $class, array $arguments) use (&$createdObjects): object {
                $createdObjects[] = [$class, $arguments];

                return (object)['class' => $class, 'arguments' => $arguments];
            },
            phpArrayFileLoader: static function (string $path, mixed $default = null) use (&$loadedPhpArrays): array {
                $loadedPhpArrays[] = [$path, $default];

                return ['loaded' => $path];
            },
            bootstrapConfigLoader: static function (context $context, ?string $configPath = null) use (&$loadedBootstrapConfigs): void {
                $loadedBootstrapConfigs[] = [$context, $configPath];
            },
            bootstrapErrorHandlerSetup: static function (context $context, callable $handler) use (&$loadedBootstrapErrorHandlers): void {
                $loadedBootstrapErrorHandlers[] = [$context, $handler];
            },
            phpRuntimeSettingsFactory: static fn(): object => $phpRuntimeSettings,
            errorHandlerRegistrar: static function (callable $handler) use (&$registeredHandlers): void {
                $registeredHandlers[] = $handler;
            },
            errorHandlerSetup: static function (callable $handler, string $defaultTimezone) use (&$errorHandlerCalls): void {
                $errorHandlerCalls[] = [$handler, $defaultTimezone];
            },
            errorLogger: static function (string $message, string $logDir) use (&$errorLogCalls): void {
                $errorLogCalls[] = [$message, $logDir];
            },
            defaultFactoriesFactory: self::defaultFactoriesFactory()
        );
        $context->state()->setLogDir('/tmp/php-fan-bootstrap-log');
        $handler = static fn(): null => null;

        $this->assertSame($input, $context->requestInput());
        $this->assertSame($runtime, $context->bootstrapRuntime());
        $this->assertSame($zendLoader, $context->zendAutoloaderLoader());
        $this->assertSame($bootstrapLoaderFileStorage, $context->bootstrapLoaderFileStorage());
        $this->assertEquals((object)['class' => 'BootstrapThing', 'arguments' => ['config.php']], $context->createBootstrapObject('BootstrapThing', ['config.php']));
        $this->assertSame([['BootstrapThing', ['config.php']]], $createdObjects);
        $this->assertSame(['loaded' => 'bootstrap.php'], $context->loadPhpArrayFile('bootstrap.php', []));
        $this->assertSame([['bootstrap.php', []]], $loadedPhpArrays);
        $context->loadBootstrapConfig('bootstrap.php');
        $this->assertSame([[$context, 'bootstrap.php']], $loadedBootstrapConfigs);
        $context->setupBootstrapErrorHandler($handler);
        $this->assertSame([[$context, $handler]], $loadedBootstrapErrorHandlers);
        $this->assertSame($phpRuntimeSettings, $context->phpRuntimeSettings());
        ($context->errorHandlerRegistrar())($handler);
        $this->assertSame([$handler], $registeredHandlers);
        $context->setupErrorHandler($handler, 'Europe/Warsaw');
        $this->assertSame([[$handler, 'Europe/Warsaw']], $errorHandlerCalls);
        $context->logError('message');
        $this->assertSame([['message', '/tmp/php-fan-bootstrap-log']], $errorLogCalls);
    }

    public function testContextRejectsInvalidDefaultFactories(): void
    {
        $method = new ReflectionMethod(context::class, 'injectedDefaultFactories');

        $this->assertTrue($method->isPrivate());
        $this->assertStringContainsString('Bootstrap context default factories must provide callable', file_get_contents(dirname(__DIR__, 3) . '/core/application/context.php'));
    }

    public function testContextRequiresInjectedDefaultFactoriesFactory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Bootstrap context default factories factory is not configured.');

        new context();
    }

    public function testContextUsesInjectedDefaultFactoriesFactoryForState(): void
    {
        $state = new state();
        $context = new context(
            defaultFactoriesFactory: static fn(): array => [
                'stateFactory' => static fn(): state => $state,
                'containerFactory' => static fn(): container => new container(),
                'requestInputFactory' => static fn(): object => new stdClass(),
                'bootstrapRuntimeFactory' => static fn(): object => new stdClass(),
                'zendAutoloaderLoaderFactory' => static fn(): object => new class {
                    public function load(string $zendPath): void
                    {
                    }
                },
                'bootstrapLoaderFileStorageFactory' => static fn(): object => new stdClass(),
                'bootstrapObjectFactory' => static fn(string $class, array $arguments): object => new stdClass(),
                'bootstrapConfigLoader' => static fn(context $context, ?string $configPath = null): null => null,
                'bootstrapErrorHandlerSetup' => static fn(context $context, callable $handler): null => null,
                'phpRuntimeSettingsFactory' => static fn(): object => new stdClass(),
                'errorHandlerRegistrar' => static fn(callable $handler): null => null,
                'errorHandlerSetup' => static fn(callable $handler, string $defaultTimezone): null => null,
                'errorLogger' => static fn(string $message, string $logDir): null => null,
            ]
        );

        $this->assertSame($state, $context->state());
    }

    public function testContextRejectsInvalidStateFactoryResult(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Bootstrap context state factory must return a bootstrap state.');

        new context(
            defaultFactoriesFactory: static fn(): array => [
                'stateFactory' => static fn(): object => new stdClass(),
                'containerFactory' => static fn(): container => new container(),
                'requestInputFactory' => static fn(): object => new stdClass(),
                'bootstrapRuntimeFactory' => static fn(): object => new stdClass(),
                'zendAutoloaderLoaderFactory' => static fn(): object => new class {
                    public function load(string $zendPath): void
                    {
                    }
                },
                'bootstrapLoaderFileStorageFactory' => static fn(): object => new stdClass(),
                'bootstrapObjectFactory' => static fn(string $class, array $arguments): object => new stdClass(),
                'bootstrapConfigLoader' => static fn(context $context, ?string $configPath = null): null => null,
                'bootstrapErrorHandlerSetup' => static fn(context $context, callable $handler): null => null,
                'phpRuntimeSettingsFactory' => static fn(): object => new stdClass(),
                'errorHandlerRegistrar' => static fn(callable $handler): null => null,
                'errorHandlerSetup' => static fn(callable $handler, string $defaultTimezone): null => null,
                'errorLogger' => static fn(string $message, string $logDir): null => null,
            ]
        );
    }

    private static function defaultFactoriesFactory(): callable
    {
        return new context_defaults_factory();
    }
}

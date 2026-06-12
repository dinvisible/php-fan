<?php

declare(strict_types=1);

use fan\core\di\container;
use fan\core\di\container_interface;
use fan\core\di\application_container_dependency_bundle;
use fan\core\di\application_container_dependency_provider;
use fan\core\di\application_container_factory;
use fan\core\di\application_service_factory_bundle;
use fan\core\di\application_service_factory_options;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use fan\core\di\application_adapter_registry;
use fan\core\di\application_factory_provider_defaults_provider;
use fan\core\di\application_factory_provider_defaults_provider_factory;
use fan\core\di\application_registry_defaults_provider;
use fan\core\di\application_registry_defaults_provider_factory;
use fan\core\di\application_service_creator_defaults_provider_factory;
use fan\core\di\application_service_registrar_defaults_provider_factory;
use fan\core\service\bootstrap_runtime;


#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class FunctionsServiceContainerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->loadCoreFunctions();
    }

    public function testDefaultContainerRegistersFirstServiceLayer(): void
    {
        $factory = $this->applicationContainerFactory();
        $container = $factory->create();

        foreach ([
            'config',
            'pear_http_session_loader',
            'php_array_file_loader',
            'serializer_operations',
            'warning_capture',
            'image_metadata_reader',
            'image_resource_factory',
            'image_canvas_operations',
            'image_output_writer',
            'image_source_file_storage',
            'plain_file_storage',
            'matcher_route_file_storage',
            'upload_size_limit_provider',
            'error_log_writer',
            'restore_password_log_storage',
            'block_file_storage',
            'meta_file_storage',
            'tab_alias_file_storage',
            'soap_wsdl_file_storage',
            'root_html_file_storage',
            'error_file_storage',
            'error_demonstrator_file_storage',
            'error_demonstrator_loader',
            'log_file_storage',
            'obfuscator_file_storage',
            'entity_file_discovery',
            'entity_description_file_storage',
            'project_tool_file_storage',
            'email_template_file_storage',
            'translation_file_storage',
            'cache_source_file_metadata',
            'config_source_file_storage',
            'header_writer',
            'error_demonstrator_factory',
            'cookie_writer',
            'data_loader',
            'request',
            'cache',
            'session',
            'role',
            'tab',
            'template_file_storage',
            'json',
            'eloquent',
            'error',
            'header',
            'locale',
            'application',
            'bootstrap_runtime',
            'service_listener_state',
            'block_context',
            'plain_file_context',
            'transfer',
        ] as $serviceName) {
            $this->assertTrue($container->has($serviceName), $serviceName . ' should be registered.');
        }

        $this->assertFalse($container->has('legacy_global_functions'));
        $this->assertFalse($container->has('database'));
        $this->assertFalse($container->has('adodb_connection_factory'));
        $this->assertFalse($container->has('adodb_session_support_loader'));
        $this->assertFalse($container->has('adodb_session_globals'));
        $this->assertFalse($container->has('adodb_session_environment'));
        $this->assertFalse($container->has('database_by_param'));
        $this->assertFalse($container->has('database_pool'));
        $this->assertFalse($container->has('database_connections'));
        $this->assertFalse($container->has('email'));
        $this->assertFalse($container->has('log'));
    }

    public function testContainerPassesArgumentsToFactory(): void
    {
        $container = new container();
        $container->factory(
            'cache',
            static fn(container_interface $container, string $type): object => (object)['type' => $type],
            false
        );

        $cache = $container->get('cache', 'session_data');

        $this->assertSame('session_data', $cache->type);
    }

    public function testGlobalFunctionBridgeNoLongerReadsContainerRegistry(): void
    {
        $code = file_get_contents(dirname(__DIR__, 2) . '/core/functions.php');

        $this->assertIsString($code);
        $this->assertStringNotContainsString('container_registry::get()', $code);
        $this->assertStringNotContainsString('legacy_global_functions', $code);
        $this->assertStringNotContainsString("define('MYSQL_", $code);
        $this->assertStringNotContainsString("defined('MYSQL_", $code);
    }

    public function testLegacyServiceWrapperFunctionsAreRemovedFromGlobalBridge(): void
    {
        $code = file_get_contents(dirname(__DIR__, 2) . '/core/functions.php');

        $this->assertIsString($code);
        $this->assertStringNotContainsString('function ge(', $code);
        $this->assertStringNotContainsString('function gr(', $code);
        $this->assertStringNotContainsString('function se(', $code);
        $this->assertStringNotContainsString('function le(', $code);
        $this->assertStringNotContainsString('function role(', $code);
        $this->assertStringNotContainsString('function msg(', $code);
        $this->assertStringNotContainsString('function transfer_out(', $code);
    }

    public function testSelectedFactoriesReceiveContainerExplicitly(): void
    {
        $code = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_container_factory.php');

        $this->assertIsString($code);
        $coreServiceCreatorCode = $this->applicationCoreServiceCreatorCode();
        foreach ([
            'createRequestService',
            'createRoleService',
            'createReflectorService',
            'createApplicationService',
            'createDebugService',
            'createHeaderService',
            'createErrorService',
            'createMatcherService',
            'createTimerService',
            'createLocaleService',
        ] as $methodName) {
            $this->assertMatchesRegularExpression(
                '/public function ' . preg_quote($methodName, '/') . '\s*\(\s*container_interface \$container/',
                $coreServiceCreatorCode,
                $methodName . ' should receive the container explicitly.'
            );
            $this->assertStringNotContainsString('self::get()->get', $this->methodSource($coreServiceCreatorCode, $methodName));
        }

        $contentServiceCreatorCode = $this->applicationContentServiceCreatorCode();
        foreach (['createTranslationService'] as $methodName) {
            $this->assertMatchesRegularExpression(
                '/public function ' . preg_quote($methodName, '/') . '\s*\(\s*container_interface \$container/',
                $contentServiceCreatorCode,
                $methodName . ' should receive the container explicitly.'
            );
            $this->assertStringNotContainsString('self::get()->get', $this->methodSource($contentServiceCreatorCode, $methodName));
        }

        $navigationServiceCreatorCode = $this->applicationNavigationServiceCreatorCode();
        foreach (['createTabService'] as $methodName) {
            $this->assertMatchesRegularExpression(
                '/public function ' . preg_quote($methodName, '/') . '\s*\(\s*container_interface \$container/',
                $navigationServiceCreatorCode,
                $methodName . ' should receive the container explicitly.'
            );
            $this->assertStringNotContainsString('self::get()->get', $this->methodSource($navigationServiceCreatorCode, $methodName));
        }

        $controllerServiceCreatorCode = $this->applicationControllerServiceCreatorCode();
        foreach (['createPlainService'] as $methodName) {
            $this->assertMatchesRegularExpression(
                '/public function ' . preg_quote($methodName, '/') . '\s*\(\s*container_interface \$container/',
                $controllerServiceCreatorCode,
                $methodName . ' should receive the container explicitly.'
            );
            $this->assertStringNotContainsString('self::get()->get', $this->methodSource($controllerServiceCreatorCode, $methodName));
        }

        $infrastructureServiceCreatorCode = $this->applicationInfrastructureServiceCreatorCode();
        foreach ([
            'createConfigService',
            'createConfigCache',
            'createCacheService',
            'createJsonService',
            'createFileSystemService',
        ] as $methodName) {
            $this->assertMatchesRegularExpression(
                '/public function ' . preg_quote($methodName, '/') . '\s*\(\s*container_interface \$container/',
                $infrastructureServiceCreatorCode,
                $methodName . ' should receive the container explicitly.'
            );
            $this->assertStringNotContainsString('self::get()->get', $this->methodSource($infrastructureServiceCreatorCode, $methodName));
        }

        $clientServiceCreatorCode = $this->applicationClientServiceCreatorCode();
        foreach ([
            'createCurlService',
            'createRestService',
            'createCookieService',
        ] as $methodName) {
            $this->assertMatchesRegularExpression(
                '/public function ' . preg_quote($methodName, '/') . '\s*\(\s*container_interface \$container/',
                $clientServiceCreatorCode,
                $methodName . ' should receive the container explicitly.'
            );
            $this->assertStringNotContainsString('self::get()->get', $this->methodSource($clientServiceCreatorCode, $methodName));
        }

        $pagerServiceCreatorCode = $this->applicationPagerServiceCreatorCode();
        foreach ([
            'createPagerService',
        ] as $methodName) {
            $this->assertMatchesRegularExpression(
                '/public function ' . preg_quote($methodName, '/') . '\s*\(\s*container_interface \$container/',
                $pagerServiceCreatorCode,
                $methodName . ' should receive the container explicitly.'
            );
            $this->assertStringNotContainsString('self::get()->get', $this->methodSource($pagerServiceCreatorCode, $methodName));
        }

        $utilityServiceCreatorCode = $this->applicationUtilityServiceCreatorCode();
        foreach ([
            'createObfuscatorService',
            'createImageModifyService',
            'createSoapService',
            'createDateService',
        ] as $methodName) {
            $this->assertMatchesRegularExpression(
                '/public function ' . preg_quote($methodName, '/') . '\s*\(\s*container_interface \$container/',
                $utilityServiceCreatorCode,
                $methodName . ' should receive the container explicitly.'
            );
            $this->assertStringNotContainsString('self::get()->get', $this->methodSource($utilityServiceCreatorCode, $methodName));
        }

        $sessionServiceCreatorCode = $this->applicationSessionServiceCreatorCode();
        foreach (['createSessionService'] as $methodName) {
            $this->assertMatchesRegularExpression(
                '/public function ' . preg_quote($methodName, '/') . '\s*\(\s*container_interface \$container/',
                $sessionServiceCreatorCode,
                $methodName . ' should receive the container explicitly.'
            );
            $this->assertStringNotContainsString('self::get()->get', $this->methodSource($sessionServiceCreatorCode, $methodName));
        }

        $serviceCreatorDefaultsProviderCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_service_creator_defaults_provider_factory.php');
        $this->assertIsString($serviceCreatorDefaultsProviderCode);
        $this->assertStringNotContainsString('emailServiceCreator', $serviceCreatorDefaultsProviderCode);

        $userServiceCreatorCode = $this->applicationUserServiceCreatorCode();
        foreach ([
            'createUserService',
            'getCurrentUserService',
            'getCurrentUserServiceChecked',
            'getCurrentUserSpace',
        ] as $methodName) {
            $this->assertMatchesRegularExpression(
                '/public function ' . preg_quote($methodName, '/') . '\s*\(\s*container_interface \$container/',
                $userServiceCreatorCode,
                $methodName . ' should receive the container explicitly.'
            );
            $this->assertStringNotContainsString('self::get()->get', $this->methodSource($userServiceCreatorCode, $methodName));
        }
    }

    public function testDefaultServiceGraphMovedOutOfStaticRegistry(): void
    {
        $contextContainerFactoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/context_container_factory.php');
        $contextDefaultsFactoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/context_defaults_factory.php');
        $contextCoreDefaultsFactoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/context_core_defaults_provider_factory.php');
        $factoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_container_factory.php');

        $this->assertIsString($contextContainerFactoryCode);
        $this->assertIsString($contextDefaultsFactoryCode);
        $this->assertIsString($contextCoreDefaultsFactoryCode);
        $this->assertIsString($factoryCode);
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/container_registry.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/container_registry_state.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/container_registry_state_factory.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/container_provider.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/container_provider_factory.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/application/container_registry_state_factory.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/application/container_registry_state_setter.php');
        $this->assertStringNotContainsString('containerRegistryStateFactory', $contextContainerFactoryCode);
        $this->assertStringNotContainsString('containerRegistryStateSetter', $contextContainerFactoryCode);
        $this->assertStringNotContainsString('container_registry_state', $contextContainerFactoryCode);
        $this->assertStringNotContainsString('\fan\core\di\container_registry::setState($state);', $contextContainerFactoryCode);
        $this->assertStringContainsString('$contextCoreDefaults = (new context_core_defaults_provider_factory())();', $contextDefaultsFactoryCode);
        $this->assertStringContainsString('$coreDefaults = $contextCoreDefaults();', $contextDefaultsFactoryCode);
        $this->assertStringNotContainsString('$coreDefaults = (new context_core_defaults_factory())();', $contextDefaultsFactoryCode);
        $this->assertStringContainsString('$contextSupportDefaults = (new context_support_defaults_provider_factory())();', $contextDefaultsFactoryCode);
        $this->assertStringContainsString('$supportDefaults = $contextSupportDefaults();', $contextDefaultsFactoryCode);
        $this->assertStringContainsString('$contextErrorHandlingDefaults = (new context_error_handling_defaults_provider_factory())();', $contextDefaultsFactoryCode);
        $this->assertStringContainsString('$errorHandlingDefaults = $contextErrorHandlingDefaults();', $contextDefaultsFactoryCode);
        $this->assertStringContainsString('$contextStateDefaults = new bootstrap_state_defaults_factory();', $contextCoreDefaultsFactoryCode);
        $this->assertStringContainsString("'stateFactory' => \$contextStateDefaults->stateFactory()", $contextCoreDefaultsFactoryCode);
        $this->assertStringContainsString('$contextApplicationContainerDefaults = (new application_container_defaults_provider_factory())();', $contextCoreDefaultsFactoryCode);
        $this->assertStringContainsString('new context_container_defaults_factory(', $contextCoreDefaultsFactoryCode);
        $this->assertStringContainsString('static fn(callable $applicationContainerFactory): context_container_factory => new context_container_factory(', $contextCoreDefaultsFactoryCode);
        $this->assertStringContainsString('$contextApplicationContainerDefaults->containerFactory()', $contextCoreDefaultsFactoryCode);
        $this->assertStringContainsString('$contextRequestInputDefaults = new bootstrap_request_input_defaults_factory();', $contextCoreDefaultsFactoryCode);
        $this->assertStringContainsString("'requestInputFactory' => \$contextRequestInputDefaults->requestInputFactory()", $contextCoreDefaultsFactoryCode);
        $this->assertStringContainsString('$contextRuntimeDefaults = new bootstrap_runtime_defaults_factory();', $contextCoreDefaultsFactoryCode);
        $this->assertStringContainsString("'bootstrapRuntimeFactory' => \$contextRuntimeDefaults()", $contextCoreDefaultsFactoryCode);
        $this->assertStringNotContainsString('$applicationContainerFactory = $this->applicationContainerFactory();', $contextDefaultsFactoryCode);
        $this->assertStringNotContainsString('$containerRegistryStateFactory = new container_registry_state_factory($applicationContainerFactory);', $contextDefaultsFactoryCode);
        $this->assertStringNotContainsString('new application_service_factory_options(', $contextDefaultsFactoryCode);
        $this->assertStringNotContainsString('application_service_factory_bundle::fromProviders(', $contextDefaultsFactoryCode);
        $this->assertStringNotContainsString('new \fan\core\di\application_container_factory($factoryBundle, $dependencyBundle)', $contextDefaultsFactoryCode);
        $this->assertStringNotContainsString('$containerRegistryStateSetter = new container_registry_state_setter();', $contextDefaultsFactoryCode);
        $this->assertStringNotContainsString('\fan\core\di\container_registry::setState($state);', $contextDefaultsFactoryCode);
        $this->assertStringContainsString('private application_container_dependency_bundle $dependencyBundle;', $factoryCode);
        $this->assertStringContainsString('application_service_factory_bundle $factoryBundle,', $factoryCode);
        $this->assertStringNotContainsString('application_service_factory_bundle::fromProviders(', $factoryCode);
        $this->assertStringContainsString(
            'public application_service_graph_registrar $serviceGraphRegistrar,',
            $this->applicationContainerDependencyBundleCode()
        );
        $this->assertStringContainsString('$serviceGraphRegistrar = $dependencyBundle->serviceGraphRegistrar;', $factoryCode);
        $this->assertStringContainsString('application_service_graph_registration_context::fromBundles(', $factoryCode);
        $this->assertStringContainsString('$serviceGraphRegistrar->register(', $factoryCode);
        $this->assertStringContainsString('$this->userServiceRegistrar->register(', $this->applicationServiceGraphRegistrarCode());
        $this->assertStringNotContainsString('->factory(', $this->applicationServiceGraphRegistrarCode());
        $this->assertStringContainsString('application_service_graph_registration_context $context', $this->applicationServiceGraphRegistrarCode());
        $this->assertStringNotContainsString('$coreServiceCreator = $dependencyBundle->coreServiceCreator;', $factoryCode);
    }

    public function testApplicationContainerFactoryAcceptsInjectedRequestInputFactory(): void
    {
        $requestInput = new class {
        };
        $calls = 0;
        $factory = $this->applicationContainerFactory(new application_service_factory_options(
            requestInputFactory: static function () use ($requestInput, &$calls): object {
                ++$calls;

                return $requestInput;
            }
        ));

        $container = $factory->create();

        $this->assertSame($requestInput, $container->get('request_input'));
        $this->assertSame($requestInput, $container->get('request_input'));
        $this->assertSame(1, $calls);
    }

    public function testApplicationContainerFactoryAcceptsInjectedPhpArrayFileLoader(): void
    {
        $calls = [];
        $factory = $this->applicationContainerFactory(new application_service_factory_options(
            phpArrayFileLoader: static function (string $path, mixed $default = null) use (&$calls): mixed {
                $calls[] = [$path, $default];

                return ['loaded' => $path];
            }
        ));

        $container = $factory->create();
        $loader = $container->get('php_array_file_loader');

        $this->assertIsCallable($loader);
        $this->assertSame(['loaded' => 'config.php'], $loader('config.php', []));
        $this->assertSame([['config.php', []]], $calls);
    }

    public function testApplicationContainerFactoryAcceptsInjectedSerializerOperationsFactory(): void
    {
        $serializerOperations = new class {
            public function stableKeyEncoder(): callable
            {
                return static fn(mixed $value): string => 'stable-key';
            }
        };
        $receivedWarningCapture = null;
        $calls = 0;
        $factory = $this->applicationContainerFactory(new application_service_factory_options(
            serializerOperationsFactory: static function (object $warningCapture) use ($serializerOperations, &$receivedWarningCapture, &$calls): object {
                ++$calls;
                $receivedWarningCapture = $warningCapture;

                return $serializerOperations;
            }
        ));

        $container = $factory->create();

        $this->assertSame($serializerOperations, $container->get('serializer_operations'));
        $this->assertSame($container->get('warning_capture'), $receivedWarningCapture);
        $this->assertSame(1, $calls);
    }

    public function testApplicationContainerFactoryDoesNotCreateConcreteRequestInputDirectly(): void
    {
        $factoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_container_factory.php');
        $supportRegistrarCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_service_registrar.php');
        $dependencyProviderCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_container_dependency_provider.php');
        $factoryProviderDefaultsProviderFactoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_factory_provider_defaults_provider_factory.php');
        $runtimeDefaultsProviderCode = $this->applicationRuntimeFactoryDefaultsProviderCode();
        $runtimeDefaultsProviderFactoryCode = $this->applicationRuntimeFactoryDefaultsProviderFactoryCode();

        $this->assertIsString($factoryCode);
        $this->assertIsString($supportRegistrarCode);
        $this->assertIsString($dependencyProviderCode);
        $this->assertIsString($factoryProviderDefaultsProviderFactoryCode);
        $this->assertStringContainsString('public \Closure $requestInputFactory,', $this->applicationServiceFactoryBundleCode());
        $this->assertStringContainsString(
            'public application_support_service_registrar $supportServiceRegistrar,',
            $this->applicationContainerDependencyBundleCode()
        );
        $this->assertStringContainsString('$supportServiceRegistrar = $dependencyBundle->supportServiceRegistrar;', $factoryCode);
        $this->assertStringContainsString('$supportServiceRegistrar->register(', $factoryCode);
        $this->assertStringContainsString('Request input factory must return an object.', $supportRegistrarCode);
        $this->assertStringContainsString(
            'new bootstrap_request_input_defaults_factory()->requestInputFactory()',
            $runtimeDefaultsProviderFactoryCode
        );
        $this->assertStringContainsString(
            '(new application_runtime_factory_defaults_provider_factory())($adapterRegistry)',
            $factoryProviderDefaultsProviderFactoryCode
        );
        $this->assertStringNotContainsString(
            'return new bootstrap_request_input_defaults_factory()->requestInputFactory();',
            $runtimeDefaultsProviderCode
        );
        $this->assertStringNotContainsString(
            '\fan\core\bootstrap\bootstrap_request_input_defaults_factory::requestInputFactory()',
            $dependencyProviderCode
        );
        $this->assertStringNotContainsString('new \fan\core\runtime\request_input_defaults_factory(', $dependencyProviderCode);
        $this->assertStringNotContainsString('new \fan\core\runtime\request_input_factory()', $this->applicationRuntimeFactoryProviderCode());
        $this->assertStringNotContainsString('new \fan\core\service\request_input()', $factoryCode);
    }

    public function testApplicationContainerFactoryDoesNotCallPhpArrayFileStaticAdapterDirectly(): void
    {
        $factoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_container_factory.php');
        $adapterRegistryCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_adapter_registry.php');
        $adapterDefaultsProviderCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_adapter_registry_defaults_provider.php');
        $coreAdapterDefaultsProviderCode = $this->applicationCoreAdapterDefaultsProviderCode();
        $registryDefaultsProviderFactoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_registry_defaults_provider_factory.php');

        $this->assertIsString($factoryCode);
        $this->assertIsString($adapterRegistryCode);
        $this->assertIsString($adapterDefaultsProviderCode);
        $this->assertIsString($coreAdapterDefaultsProviderCode);
        $this->assertIsString($registryDefaultsProviderFactoryCode);
        $this->assertStringContainsString('public \Closure $phpArrayFileLoader,', $this->applicationServiceFactoryBundleCode());
        $this->assertStringContainsString(
            'new php_array_file_loader()',
            $registryDefaultsProviderFactoryCode
        );
        $this->assertStringNotContainsString('new php_array_file_loader()', $coreAdapterDefaultsProviderCode);
        $this->assertStringNotContainsString('new php_array_file_loader()', $adapterDefaultsProviderCode);
        $this->assertStringContainsString('$this->phpArrayFileLoader = $defaultsProvider->phpArrayFileLoader();', $adapterRegistryCode);
        $this->assertStringContainsString('callable $configuredConstructionBoundary,', $this->applicationRuntimeFactoryProviderCode());
        $this->assertStringContainsString('callable $requestInputFactory,', $this->applicationRuntimeFactoryProviderCode());
        $this->assertStringContainsString('callable $phpArrayFileLoader,', $this->applicationRuntimeFactoryProviderCode());
        $this->assertStringContainsString('callable $serializerOperationsFactory', $this->applicationRuntimeFactoryProviderCode());
        $this->assertStringNotContainsString('new php_array_file_loader()', $this->applicationRuntimeFactoryProviderCode());
        $this->assertStringNotContainsString('php_array_file::load', $factoryCode);
    }

    public function testApplicationContainerFactoryDoesNotCallSafeSerializerStaticAdapterDirectly(): void
    {
        $factoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_container_factory.php');
        $supportRegistrarCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_service_registrar.php');
        $adapterRegistryCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_adapter_registry.php');
        $adapterDefaultsProviderCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_adapter_registry_defaults_provider.php');
        $coreAdapterDefaultsProviderCode = $this->applicationCoreAdapterDefaultsProviderCode();
        $registryDefaultsProviderFactoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_registry_defaults_provider_factory.php');

        $this->assertIsString($factoryCode);
        $this->assertIsString($supportRegistrarCode);
        $this->assertIsString($adapterRegistryCode);
        $this->assertIsString($adapterDefaultsProviderCode);
        $this->assertIsString($coreAdapterDefaultsProviderCode);
        $this->assertIsString($registryDefaultsProviderFactoryCode);
        $this->assertStringContainsString('public \Closure $serializerOperationsFactory,', $this->applicationServiceFactoryBundleCode());
        $this->assertStringContainsString('$dependencyProvider = $dependencyBundle->dependencyProvider;', $factoryCode);
        $this->assertStringContainsString('$adapterRegistry = $dependencyProvider->applicationAdapterRegistry();', $factoryCode);
        $this->assertStringContainsString('$warningCapture = $adapterRegistry->warningCapture();', $factoryCode);
        $this->assertStringContainsString(
            'new safe_serializer_operations(',
            $registryDefaultsProviderFactoryCode
        );
        $this->assertStringNotContainsString('new safe_serializer_operations(', $coreAdapterDefaultsProviderCode);
        $this->assertStringNotContainsString('new safe_serializer_operations(', $adapterDefaultsProviderCode);
        $this->assertStringContainsString('$this->serializerOperationsFactory = \Closure::fromCallable($defaultsProvider->serializerOperationsFactory());', $adapterRegistryCode);
        $this->assertStringNotContainsString('new safe_serializer_operations($warningCapture)', $this->applicationRuntimeFactoryProviderCode());
        $this->assertStringContainsString('service_id::SERIALIZER_OPERATIONS', $supportRegistrarCode);
        $this->assertStringNotContainsString('safe_serializer::', $factoryCode);
    }

    public function testApplicationContainerFactoryUsesInjectedBlockFactories(): void
    {
        $factoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_container_factory.php');
        $dependencyProviderCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_container_dependency_provider.php');
        $factoryProviderDefaultsProviderFactoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_factory_provider_defaults_provider_factory.php');
        $subDefaultsProviderFactoryCode = $this->applicationServiceSubFactoryDefaultsProviderFactoryCode();

        $this->assertIsString($factoryCode);
        $this->assertIsString($dependencyProviderCode);
        $this->assertIsString($factoryProviderDefaultsProviderFactoryCode);
        $this->assertStringContainsString('public \Closure $blockFactory,', $this->applicationServiceFactoryBundleCode());
        $this->assertStringContainsString('public \Closure $blockExceptionFactory,', $this->applicationServiceFactoryBundleCode());
        $this->assertStringContainsString(
            'new block_factory($configuredServiceFactory)',
            $subDefaultsProviderFactoryCode
        );
        $this->assertStringNotContainsString(
            'new block_factory($configuredServiceFactory)',
            $factoryProviderDefaultsProviderFactoryCode
        );
        $this->assertStringContainsString(
            'new block_exception_factory($configuredServiceFactory)',
            $subDefaultsProviderFactoryCode
        );
        $this->assertStringNotContainsString(
            'new block_exception_factory($configuredServiceFactory)',
            $factoryProviderDefaultsProviderFactoryCode
        );
        $this->assertStringNotContainsString('new block_factory($configuredServiceFactory)', $this->applicationServiceSubFactoryDefaultsProviderCode());
        $this->assertStringNotContainsString('new block_exception_factory($configuredServiceFactory)', $this->applicationServiceSubFactoryDefaultsProviderCode());
        $this->assertStringNotContainsString(
            'new \fan\core\di\block_factory($configuredServiceFactory)',
            $this->applicationServiceFactoryRegistryCode()
        );
        $this->assertStringNotContainsString(
            'new \fan\core\di\block_exception_factory($configuredServiceFactory)',
            $this->applicationServiceFactoryRegistryCode()
        );
        $this->assertStringNotContainsString('new $blockClass(', $factoryCode);
        $this->assertStringNotContainsString('new $exceptionClass(', $factoryCode);
    }

    public function testApplicationContainerFactoryDoesNotUseTemplateTypeFactory(): void
    {
        $factoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_container_factory.php');
        $dependencyProviderCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_container_dependency_provider.php');
        $factoryProviderDefaultsProviderFactoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_factory_provider_defaults_provider_factory.php');
        $subDefaultsProviderFactoryCode = $this->applicationServiceSubFactoryDefaultsProviderFactoryCode();

        $this->assertIsString($factoryCode);
        $this->assertIsString($dependencyProviderCode);
        $this->assertIsString($factoryProviderDefaultsProviderFactoryCode);
        $this->assertStringNotContainsString('public \Closure $templateTypeFactory,', $this->applicationServiceFactoryBundleCode());
        $this->assertStringNotContainsString('new template_type_factory($configuredServiceFactory)', $factoryProviderDefaultsProviderFactoryCode);
        $this->assertStringNotContainsString('new template_type_factory($configuredServiceFactory)', $subDefaultsProviderFactoryCode);
        $this->assertStringNotContainsString(
            'new \fan\core\di\template_type_factory($configuredServiceFactory)',
            $this->applicationServiceFactoryRegistryCode()
        );
        $this->assertStringNotContainsString('createTemplateService', $this->applicationContentServiceCreatorCode());
        $this->assertStringNotContainsString('new $templateClass(...$dependencies)', $factoryCode);
    }

    public function testApplicationContainerFactoryUsesInjectedRequestServiceFactory(): void
    {
        $factoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_container_factory.php');
        $dependencyProviderCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_container_dependency_provider.php');
        $factoryProviderDefaultsProviderFactoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_factory_provider_defaults_provider_factory.php');
        $coreDefaultsProviderFactoryCode = $this->applicationCoreServiceFactoryDefaultsProviderFactoryCode();

        $this->assertIsString($factoryCode);
        $this->assertIsString($dependencyProviderCode);
        $this->assertIsString($factoryProviderDefaultsProviderFactoryCode);
        $this->assertStringContainsString('public \Closure $requestServiceFactory,', $this->applicationServiceFactoryBundleCode());
        $this->assertStringContainsString('public \Closure $configuredServiceFactory', $this->applicationServiceFactoryBundleCode());
        $this->assertStringContainsString(
            'new request_service_factory(',
            $coreDefaultsProviderFactoryCode
        );
        $this->assertStringNotContainsString('new request_service_factory($configuredServiceFactory)', $factoryProviderDefaultsProviderFactoryCode);
        $this->assertStringNotContainsString(
            'new \fan\core\di\request_service_factory($configuredServiceFactory)',
            $this->applicationServiceFactoryRegistryCode()
        );
        $this->assertStringContainsString('$coreServiceCreator->createRequestService($container, $context->requestServiceFactory)', $this->applicationCoreServiceRegistrarCode());
        $this->assertStringContainsString('return $requestServiceFactory(', $this->methodSource($this->applicationCoreServiceCreatorCode(), 'createRequestService'));
        $this->assertStringNotContainsString('return new $className(', $this->methodSource($this->applicationCoreServiceCreatorCode(), 'createRequestService'));
    }

    public function testApplicationContainerFactoryUsesInjectedRoleServiceFactory(): void
    {
        $factoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_container_factory.php');
        $dependencyProviderCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_container_dependency_provider.php');
        $factoryProviderDefaultsProviderFactoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_factory_provider_defaults_provider_factory.php');
        $coreDefaultsProviderFactoryCode = $this->applicationCoreServiceFactoryDefaultsProviderFactoryCode();

        $this->assertIsString($factoryCode);
        $this->assertIsString($dependencyProviderCode);
        $this->assertIsString($factoryProviderDefaultsProviderFactoryCode);
        $this->assertStringContainsString('public \Closure $roleServiceFactory,', $this->applicationServiceFactoryBundleCode());
        $this->assertStringContainsString(
            'new role_service_factory(',
            $coreDefaultsProviderFactoryCode
        );
        $this->assertStringNotContainsString(
            'new reflection_class_factory()',
            $coreDefaultsProviderFactoryCode
        );
        $this->assertStringNotContainsString('new role_service_factory($configuredServiceFactory)', $factoryProviderDefaultsProviderFactoryCode);
        $this->assertStringNotContainsString(
            'new \fan\core\di\role_service_factory($configuredServiceFactory)',
            $this->applicationServiceFactoryRegistryCode()
        );
        $this->assertStringContainsString('$coreServiceCreator->createRoleService($container, $context->roleServiceFactory)', $this->applicationCoreServiceRegistrarCode());
        $this->assertStringContainsString('return $roleServiceFactory(', $this->methodSource($this->applicationCoreServiceCreatorCode(), 'createRoleService'));
        $this->assertStringNotContainsString('return new $className(', $this->methodSource($this->applicationCoreServiceCreatorCode(), 'createRoleService'));
    }

    public function testDatabaseEngineGraphDoesNotRegisterRemovedAdodbAdapter(): void
    {
        $factoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_container_factory.php');
        $adapterRegistryCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_adapter_registry.php');
        $adapterDefaultsProviderCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_adapter_registry_defaults_provider.php');
        $runtimeDefaultsProviderCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_runtime_adapter_defaults_provider.php');
        $registryDefaultsProviderFactoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_registry_defaults_provider_factory.php');
        $dependencyProviderCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_container_dependency_provider.php');
        $factoryProviderDefaultsProviderFactoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_factory_provider_defaults_provider_factory.php');
        $engineDefaultsProviderFactoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_service_engine_factory_defaults_provider_factory.php');
        $runtimeDefaultsProviderFactoryCode = $this->applicationRuntimeFactoryDefaultsProviderFactoryCode();

        $this->assertIsString($factoryCode);
        $this->assertIsString($adapterRegistryCode);
        $this->assertIsString($adapterDefaultsProviderCode);
        $this->assertIsString($runtimeDefaultsProviderCode);
        $this->assertIsString($registryDefaultsProviderFactoryCode);
        $this->assertIsString($dependencyProviderCode);
        $this->assertIsString($factoryProviderDefaultsProviderFactoryCode);
        $this->assertIsString($engineDefaultsProviderFactoryCode);
        $this->assertStringNotContainsString('databaseEngineFactory', $this->applicationServiceFactoryBundleCode());
        $this->assertStringNotContainsString(
            'new database_engine_factory(',
            $engineDefaultsProviderFactoryCode
        );
        $this->assertStringNotContainsString(
            'new database_engine_factory($configuredServiceFactory)',
            $factoryProviderDefaultsProviderFactoryCode
        );
        $this->assertStringNotContainsString(
            'new \fan\core\di\database_engine_factory($configuredServiceFactory)',
            $this->applicationServiceFactoryRegistryCode()
        );
        $this->assertStringNotContainsString('private static function createDatabaseEngine(', $factoryCode);
        foreach ([
            $factoryCode,
            $adapterRegistryCode,
            $adapterDefaultsProviderCode,
            $runtimeDefaultsProviderCode,
            $registryDefaultsProviderFactoryCode,
        ] as $source) {
            $this->assertStringNotContainsString('adodb', strtolower($source));
        }
    }

    public function testBootstrapRuntimeReceivesBootstrapOperationsFromInjectedFactory(): void
    {
        $calls = 0;
        $runtimeFactoryCalls = 0;
        $serviceEngineFactory = static fn(string $class): object => (object)['class' => $class];
        $capturedServiceEngineFactory = null;
        $factory = $this->applicationContainerFactory(new application_service_factory_options(
            bootstrapOperationsFactory: static function () use (&$calls): array {
                ++$calls;

                return [
                    'logError' => static fn(string $message): null => null,
                ];
            },
            bootstrapRuntimeServiceFactory: static function (
                ?object $serviceListenerState = null,
                ?object $serviceSingleState = null,
                ?object $viewLoaderState = null,
                ?object $metaMakerState = null,
                ?object $specFileImageRowState = null,
                ?callable $serviceEngineFactory = null,
                array $bootstrapOperations = []
            ) use (&$runtimeFactoryCalls, &$capturedServiceEngineFactory): object {
                ++$runtimeFactoryCalls;
                $capturedServiceEngineFactory = $serviceEngineFactory;

                return new bootstrap_runtime(
                    $serviceListenerState,
                    $serviceSingleState,
                    $viewLoaderState,
                    $metaMakerState,
                    $specFileImageRowState,
                    $serviceEngineFactory,
                    $bootstrapOperations
                );
            },
            serviceEngineFactory: $serviceEngineFactory
        ));

        $container = $factory->create();

        $this->assertInstanceOf(bootstrap_runtime::class, $container->get('bootstrap_runtime'));
        $this->assertSame(1, $calls);
        $this->assertSame(1, $runtimeFactoryCalls);
        $this->assertSame($serviceEngineFactory, $capturedServiceEngineFactory);
    }

    public function testApplicationFactoryProvidesBootstrapOperationsDependencyExplicitly(): void
    {
        $factoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_container_factory.php');
        $supportRegistrarCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_service_registrar.php');
        $dependencyProviderCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_container_dependency_provider.php');
        $factoryProviderDefaultsProviderFactoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_factory_provider_defaults_provider_factory.php');
        $engineDefaultsProviderFactoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_service_engine_factory_defaults_provider_factory.php');
        $runtimeDefaultsProviderFactoryCode = $this->applicationRuntimeFactoryDefaultsProviderFactoryCode();

        $this->assertIsString($factoryCode);
        $this->assertIsString($supportRegistrarCode);
        $this->assertIsString($dependencyProviderCode);
        $this->assertIsString($factoryProviderDefaultsProviderFactoryCode);
        $this->assertIsString($engineDefaultsProviderFactoryCode);
        $this->assertStringContainsString('public \Closure $bootstrapOperationsFactory,', $this->applicationServiceFactoryBundleCode());
        $this->assertStringContainsString('public \Closure $bootstrapRuntimeServiceFactory,', $this->applicationServiceFactoryBundleCode());
        $this->assertStringContainsString('public \Closure $serviceEngineFactory,', $this->applicationServiceFactoryBundleCode());
        $this->assertStringContainsString('public \Closure $configuredServiceFactory', $this->applicationServiceFactoryBundleCode());
        $this->assertStringContainsString(
            'public application_support_service_registrar $supportServiceRegistrar,',
            $this->applicationContainerDependencyBundleCode()
        );
        $this->assertStringContainsString(
            '(new application_service_engine_factory_defaults_provider_factory())($adapterRegistry)',
            $this->applicationServiceFactoryDefaultsProviderFactoryCode()
        );
        $this->assertStringContainsString(
            'bootstrap_runtime_service_factory_defaults_provider_factory())()',
            $engineDefaultsProviderFactoryCode
        );
        $this->assertStringNotContainsString(
            'bootstrap_runtime_service_factory_defaults_provider_factory())()',
            $factoryProviderDefaultsProviderFactoryCode
        );
        $this->assertStringNotContainsString(
            'bootstrap_runtime_service_defaults_provider_factory())()',
            $factoryProviderDefaultsProviderFactoryCode
        );
        $this->assertStringContainsString(
            'new service_engine_factory($configuredServiceFactory)',
            $engineDefaultsProviderFactoryCode
        );
        $this->assertStringNotContainsString(
            'new service_engine_factory($configuredServiceFactory)',
            $factoryProviderDefaultsProviderFactoryCode
        );
        $this->assertStringContainsString(
            'return $this->bootstrapRuntimeServiceFactoryFactory;',
            $this->applicationServiceEngineFactoryDefaultsProviderCode()
        );
        $this->assertStringContainsString(
            'return $this->serviceEngineFactoryFactory;',
            $this->applicationServiceEngineFactoryDefaultsProviderCode()
        );
        $this->assertStringNotContainsString(
            'new \fan\core\di\bootstrap_runtime_service_factory()',
            $this->applicationServiceFactoryRegistryCode()
        );
        $this->assertStringNotContainsString(
            'new \fan\core\di\service_engine_factory($configuredServiceFactory)',
            $this->applicationServiceFactoryRegistryCode()
        );
        $this->assertStringContainsString('return static fn(): array => [];', $this->applicationRuntimeFactoryProviderCode());
        $this->assertStringNotContainsString(
            'new \fan\core\bootstrap\bootstrap_operations_factory(',
            $this->applicationRuntimeFactoryProviderCode()
        );
        $this->assertStringNotContainsString(
            'new \fan\core\bootstrap\bootstrap_static_operations()',
            $this->applicationRuntimeFactoryProviderCode()
        );
        $this->assertStringContainsString('Bootstrap operations factory must return an array.', $supportRegistrarCode);
        $this->assertStringNotContainsString('static fn(string $class): object => new $class()', $factoryCode);
        $this->assertStringNotContainsString('new \fan\core\service\bootstrap_runtime(', $factoryCode);
        $this->assertStringNotContainsString('new \fan\core\service\bootstrap_runtime(', $this->applicationServiceEngineFactoryDefaultsProviderCode());
        $this->assertStringNotContainsString(
            "require_once dirname(__DIR__) . '/bootstrap/' . \$fileName;",
            $runtimeDefaultsProviderFactoryCode
        );
        $this->assertStringContainsString(
            '(new application_runtime_factory_defaults_provider_factory())($adapterRegistry)',
            $factoryProviderDefaultsProviderFactoryCode
        );
        $this->assertStringNotContainsString(
            "require_once dirname(__DIR__) . '/bootstrap/' . \$fileName;",
            $factoryProviderDefaultsProviderFactoryCode
        );
        $this->assertStringNotContainsString('new \fan\core\service\bootstrap_runtime(', $dependencyProviderCode);
        $this->assertStringNotContainsString('\bootstrap::', $factoryCode);
    }
    public function testRemovedAdodbSessionGlobalsAreNotRegistered(): void
    {
        $factoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_container_factory.php');
        $adapterRegistryCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_adapter_registry.php');
        $adapterDefaultsProviderCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_adapter_registry_defaults_provider.php');
        $sessionDefaultsProviderCode = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_session_adapter_defaults_provider.php');
        $registryDefaultsProviderFactoryCode = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_registry_defaults_provider_factory.php');

        $this->assertIsString($factoryCode);
        $this->assertIsString($adapterRegistryCode);
        $this->assertIsString($adapterDefaultsProviderCode);
        $this->assertIsString($sessionDefaultsProviderCode);
        $this->assertIsString($registryDefaultsProviderFactoryCode);
        foreach ([
            $factoryCode,
            $adapterRegistryCode,
            $adapterDefaultsProviderCode,
            $sessionDefaultsProviderCode,
            $registryDefaultsProviderFactoryCode,
        ] as $source) {
            $this->assertStringNotContainsString('adodb', strtolower($source));
        }
    }
    private function loadCoreFunctions(): void
    {
        if (!function_exists('get_class_alt')) {
            require_once dirname(__DIR__, 2) . '/core/functions.php';
        }
    }

    private function methodSource(string $code, string $methodName): string
    {
        $start = strpos($code, 'private static function ' . $methodName);
        if ($start === false) {
            $start = strpos($code, 'public function ' . $methodName);
        }
        if ($start === false) {
            $start = strpos($code, 'private function ' . $methodName);
        }
        $this->assertIsInt($start);
        $nextPrivate = strpos($code, "\n    private static function ", $start + 1);
        $nextInstancePrivate = strpos($code, "\n    private function ", $start + 1);
        $nextPublic = strpos($code, "\n    public function ", $start + 1);
        $nextCandidates = array_filter([$nextPrivate, $nextInstancePrivate, $nextPublic], 'is_int');
        $next = $nextCandidates === [] ? false : min($nextCandidates);

        return $next === false ? substr($code, $start) : substr($code, $start, $next - $start);
    }

    private function applicationServiceFactoryRegistryCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/service_factory_registry.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationServiceFactoryDefaultsProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_service_factory_defaults_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationCoreAdapterDefaultsProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_core_adapter_defaults_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationStorageAdapterDefaultsProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_storage_adapter_defaults_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationServiceEngineFactoryDefaultsProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_service_engine_factory_defaults_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationServiceSubFactoryDefaultsProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_service_sub_factory_defaults_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationServiceSubFactoryDefaultsProviderFactoryCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_service_sub_factory_defaults_provider_factory.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationServiceFactoryDefaultsProviderFactoryCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_service_factory_defaults_provider_factory.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationCoreServiceFactoryDefaultsProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_core_service_factory_defaults_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationCoreServiceFactoryDefaultsProviderFactoryCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_core_service_factory_defaults_provider_factory.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationClientServiceFactoryDefaultsProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_client_service_factory_defaults_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationInfrastructureServiceFactoryDefaultsProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_infrastructure_service_factory_defaults_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationPagerServiceFactoryDefaultsProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_pager_service_factory_defaults_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationUtilityServiceFactoryDefaultsProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_utility_service_factory_defaults_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationNavigationServiceFactoryDefaultsProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_navigation_service_factory_defaults_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationContentServiceFactoryDefaultsProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_content_service_factory_defaults_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationSessionServiceFactoryDefaultsProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_session_service_factory_defaults_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationUserServiceFactoryDefaultsProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_user_service_factory_defaults_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationContainerFactory(?application_service_factory_options $factoryOptions = null): application_container_factory
    {
        $dependencyProvider = new application_container_dependency_provider(
            static fn(): application_registry_defaults_provider => (new application_registry_defaults_provider_factory())(),
            static fn(
                application_adapter_registry $adapterRegistry
            ): application_factory_provider_defaults_provider => (new application_factory_provider_defaults_provider_factory())($adapterRegistry),
            static fn(): array => (new application_service_registrar_defaults_provider_factory())(),
            static fn(): array => (new application_service_creator_defaults_provider_factory())()
        );
        $factoryOptions ??= new application_service_factory_options();

        return new application_container_factory(
            application_service_factory_bundle::fromProviders(
                $dependencyProvider->applicationRuntimeFactoryProvider(),
                $dependencyProvider->applicationModelFactoryProvider(),
                $dependencyProvider->applicationServiceFactoryRegistry(),
                $factoryOptions
            ),
            application_container_dependency_bundle::fromProvider($dependencyProvider)
        );
    }

    private function applicationServiceFactoryBundleCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_service_factory_bundle.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationContainerDependencyBundleCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_container_dependency_bundle.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationRuntimeFactoryProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_runtime_factory_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationRuntimeFactoryDefaultsProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_runtime_factory_defaults_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationRuntimeFactoryDefaultsProviderFactoryCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_runtime_factory_defaults_provider_factory.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationDeferredServiceFactoryProviderCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_deferred_service_factory_provider.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationServiceGraphRegistrarCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_service_graph_registrar.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationCoreServiceRegistrarCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_core_service_registrar.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationInfrastructureServiceRegistrarCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_infrastructure_service_registrar.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationContentServiceRegistrarCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_content_service_registrar.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationNavigationServiceRegistrarCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_navigation_service_registrar.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationControllerServiceRegistrarCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_controller_service_registrar.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationClientServiceRegistrarCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_client_service_registrar.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationPagerServiceRegistrarCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_pager_service_registrar.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationUtilityServiceRegistrarCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_utility_service_registrar.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationSessionServiceRegistrarCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_session_service_registrar.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationUserServiceRegistrarCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_user_service_registrar.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationServiceGraphRegistrationContextCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_service_graph_registration_context.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationCoreServiceCreatorCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_core_service_creator.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationContentServiceCreatorCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_content_service_creator.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationNavigationServiceCreatorCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_navigation_service_creator.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationControllerServiceCreatorCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_controller_service_creator.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationInfrastructureServiceCreatorCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_infrastructure_service_creator.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationClientServiceCreatorCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_client_service_creator.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationPagerServiceCreatorCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_pager_service_creator.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationUtilityServiceCreatorCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_utility_service_creator.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationSessionServiceCreatorCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_session_service_creator.php');
        $this->assertIsString($source);

        return $source;
    }

    private function applicationUserServiceCreatorCode(): string
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_user_service_creator.php');
        $this->assertIsString($source);

        return $source;
    }
}

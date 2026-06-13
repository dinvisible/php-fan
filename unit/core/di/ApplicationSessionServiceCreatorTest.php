<?php

declare(strict_types=1);

use fan\core\di\application_session_service_creator;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;
use fan\core\service\session_state;
use fan\project\service\session;


final class ApplicationSessionServiceCreatorTest extends TestCase
{
    public function testSessionCreatorUsesDependencyBundle(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_service_creator.php');
        $dependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_service_dependencies.php');
        $contextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_context_dependencies.php');
        $factorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_factory_dependencies.php');
        $runtimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_runtime_dependencies.php');
        $applicationContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_application_context_dependencies.php');
        $configApplicationContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_config_application_context_dependencies.php');
        $applicationInstanceApplicationContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_application_instance_application_context_dependencies.php');
        $requestContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_request_context_dependencies.php');
        $requestInputRequestContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_request_input_request_context_dependencies.php');
        $requestInstanceRequestContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_request_instance_request_context_dependencies.php');
        $headerContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_header_context_dependencies.php');
        $supportFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_support_factory_dependencies.php');
        $errorFactorySupportFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_error_factory_support_factory_dependencies.php');
        $dateFactorySupportFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_date_factory_support_factory_dependencies.php');
        $stateFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_state_factory_dependencies.php');
        $sessionFactoryStateFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_session_factory_state_factory_dependencies.php');
        $cookieFactoryStateFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_cookie_factory_state_factory_dependencies.php');
        $cacheFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_cache_factory_dependencies.php');
        $nativeRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_native_runtime_dependencies.php');
        $pearHttpSessionLoaderNativeRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_pear_http_session_loader_native_runtime_dependencies.php');
        $nativeSessionNativeRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_native_session_native_runtime_dependencies.php');
        $bootstrapRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_bootstrap_runtime_dependencies.php');
        $bootstrapBootstrapRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_bootstrap_bootstrap_runtime_dependencies.php');
        $phpRuntimeSettingsBootstrapRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_php_runtime_settings_bootstrap_runtime_dependencies.php');
        $arrayRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_array_runtime_dependencies.php');

        $this->assertIsString($source);
        $this->assertIsString($dependenciesSource);
        $this->assertIsString($contextSource);
        $this->assertIsString($factorySource);
        $this->assertIsString($runtimeSource);
        $this->assertIsString($applicationContextSource);
        $this->assertIsString($configApplicationContextSource);
        $this->assertIsString($applicationInstanceApplicationContextSource);
        $this->assertIsString($requestContextSource);
        $this->assertIsString($requestInputRequestContextSource);
        $this->assertIsString($requestInstanceRequestContextSource);
        $this->assertIsString($headerContextSource);
        $this->assertIsString($supportFactorySource);
        $this->assertIsString($errorFactorySupportFactorySource);
        $this->assertIsString($dateFactorySupportFactorySource);
        $this->assertIsString($stateFactorySource);
        $this->assertIsString($sessionFactoryStateFactorySource);
        $this->assertIsString($cookieFactoryStateFactorySource);
        $this->assertIsString($cacheFactorySource);
        $this->assertIsString($nativeRuntimeSource);
        $this->assertIsString($pearHttpSessionLoaderNativeRuntimeSource);
        $this->assertIsString($nativeSessionNativeRuntimeSource);
        $this->assertIsString($bootstrapRuntimeSource);
        $this->assertIsString($bootstrapBootstrapRuntimeSource);
        $this->assertIsString($phpRuntimeSettingsBootstrapRuntimeSource);
        $this->assertIsString($arrayRuntimeSource);
        $this->assertStringContainsString('private function sessionDependencies(container_interface $container): application_session_service_dependencies', $source);
        $this->assertStringContainsString('return new application_session_service_dependencies($container);', $source);
        $this->assertStringContainsString('$sessionDependencies = $this->sessionDependencies($container);', $source);
        $this->assertStringContainsString('$sessionDependencies->requestInput()', $source);
        $this->assertStringContainsString('$sessionDependencies->headerWriter()', $source);
        $this->assertStringContainsString('$sessionDependencies->cacheFactory()', $source);
        $this->assertStringContainsString('final class application_session_service_dependencies', $dependenciesSource);
        $this->assertStringContainsString('$this->context = new application_session_context_dependencies($container);', $dependenciesSource);
        $this->assertStringContainsString('$this->factory = new application_session_factory_dependencies($container);', $dependenciesSource);
        $this->assertStringContainsString('$this->runtime = new application_session_runtime_dependencies($container);', $dependenciesSource);
        $this->assertStringContainsString('new application_session_application_context_dependencies($container)', $contextSource);
        $this->assertStringContainsString('new application_session_request_context_dependencies($container)', $contextSource);
        $this->assertStringContainsString('new application_session_header_context_dependencies($container)', $contextSource);
        $this->assertStringContainsString('return $this->applicationContext->config();', $contextSource);
        $this->assertStringContainsString('return $this->requestContext->requestInput();', $contextSource);
        $this->assertStringContainsString('return $this->headerContext->headerWriter();', $contextSource);
        $this->assertStringContainsString('new application_session_config_application_context_dependencies($container)', $applicationContextSource);
        $this->assertStringContainsString('new application_session_application_instance_application_context_dependencies($container)', $applicationContextSource);
        $this->assertStringContainsString('return $this->config->config();', $applicationContextSource);
        $this->assertStringContainsString('return $this->application->application();', $applicationContextSource);
        $this->assertStringContainsString('return $this->container->get(service_id::CONFIG);', $configApplicationContextSource);
        $this->assertStringContainsString('return $this->container->get(service_id::APPLICATION);', $applicationInstanceApplicationContextSource);
        $this->assertStringContainsString('new application_session_request_input_request_context_dependencies($container)', $requestContextSource);
        $this->assertStringContainsString('new application_session_request_instance_request_context_dependencies($container)', $requestContextSource);
        $this->assertStringContainsString('return $this->requestInput->requestInput();', $requestContextSource);
        $this->assertStringContainsString('return $this->request->request();', $requestContextSource);
        $this->assertStringContainsString('return $this->container->get(service_id::REQUEST_INPUT);', $requestInputRequestContextSource);
        $this->assertStringContainsString('return $this->container->get(service_id::REQUEST);', $requestInstanceRequestContextSource);
        $this->assertStringContainsString('return $this->container->get(service_id::HEADER_WRITER);', $headerContextSource);
        $this->assertStringContainsString('new application_session_support_factory_dependencies($container)', $factorySource);
        $this->assertStringContainsString('new application_session_state_factory_dependencies($container)', $factorySource);
        $this->assertStringContainsString('new application_session_cache_factory_dependencies($container)', $factorySource);
        $this->assertStringContainsString('return $this->support->errorFactory();', $factorySource);
        $this->assertStringContainsString('return $this->state->sessionFactory();', $factorySource);
        $this->assertStringContainsString('return $this->cache->cacheFactory();', $factorySource);
        $this->assertStringContainsString('new application_session_error_factory_support_factory_dependencies($container)', $supportFactorySource);
        $this->assertStringContainsString('new application_session_date_factory_support_factory_dependencies($container)', $supportFactorySource);
        $this->assertStringContainsString('return $this->errorFactory->errorFactory();', $supportFactorySource);
        $this->assertStringContainsString('return $this->dateFactory->dateFactory();', $supportFactorySource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::ERROR);', $errorFactorySupportFactorySource);
        $this->assertStringContainsString('return fn(string $date): mixed => $this->container->get(service_id::DATE, $date);', $dateFactorySupportFactorySource);
        $this->assertStringContainsString('new application_session_session_factory_state_factory_dependencies($container)', $stateFactorySource);
        $this->assertStringContainsString('new application_session_cookie_factory_state_factory_dependencies($container)', $stateFactorySource);
        $this->assertStringContainsString('return $this->sessionFactory->sessionFactory();', $stateFactorySource);
        $this->assertStringContainsString('return $this->cookieFactory->cookieFactory();', $stateFactorySource);
        $this->assertStringContainsString('return fn(string $namespace, string $group): mixed => $this->container->get(service_id::SESSION, $namespace, $group);', $sessionFactoryStateFactorySource);
        $this->assertStringContainsString('return fn(mixed $path, mixed $domain): mixed => $this->container->get(service_id::COOKIE, $path, $domain);', $cookieFactoryStateFactorySource);
        $this->assertStringContainsString('return fn(string $type): mixed => $this->container->get(service_id::CACHE, $type);', $cacheFactorySource);
        $this->assertStringContainsString('new application_session_native_runtime_dependencies($container)', $runtimeSource);
        $this->assertStringContainsString('new application_session_bootstrap_runtime_dependencies($container)', $runtimeSource);
        $this->assertStringContainsString('new application_session_array_runtime_dependencies($container)', $runtimeSource);
        $this->assertStringContainsString('return $this->native->pearHttpSessionLoader();', $runtimeSource);
        $this->assertStringContainsString('return $this->bootstrap->bootstrapRuntime();', $runtimeSource);
        $this->assertStringContainsString('return $this->array->arrayValueReader();', $runtimeSource);
        $this->assertStringContainsString('new application_session_pear_http_session_loader_native_runtime_dependencies($container)', $nativeRuntimeSource);
        $this->assertStringContainsString('new application_session_native_session_native_runtime_dependencies($container)', $nativeRuntimeSource);
        $this->assertStringContainsString('return $this->pearHttpSessionLoader->pearHttpSessionLoader();', $nativeRuntimeSource);
        $this->assertStringContainsString('return $this->nativeSession->nativeSession();', $nativeRuntimeSource);
        $this->assertStringContainsString('return $this->container->get(service_id::PEAR_HTTP_SESSION_LOADER);', $pearHttpSessionLoaderNativeRuntimeSource);
        $this->assertStringContainsString('return $this->container->get(service_id::NATIVE_SESSION);', $nativeSessionNativeRuntimeSource);
        $this->assertStringContainsString('new application_session_bootstrap_bootstrap_runtime_dependencies($container)', $bootstrapRuntimeSource);
        $this->assertStringContainsString('new application_session_php_runtime_settings_bootstrap_runtime_dependencies($container)', $bootstrapRuntimeSource);
        $this->assertStringContainsString('return $this->bootstrapRuntime->bootstrapRuntime();', $bootstrapRuntimeSource);
        $this->assertStringContainsString('return $this->phpRuntimeSettings->phpRuntimeSettings();', $bootstrapRuntimeSource);
        $this->assertStringContainsString('return $this->container->get(service_id::BOOTSTRAP_RUNTIME);', $bootstrapBootstrapRuntimeSource);
        $this->assertStringContainsString('return $this->container->get(service_id::PHP_RUNTIME_SETTINGS);', $phpRuntimeSettingsBootstrapRuntimeSource);
        $this->assertStringContainsString('return $this->container->get(service_id::ARRAY_VALUE_READER);', $arrayRuntimeSource);
    }

    public function testSessionCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = $this->containerWithSessionDependencies();
        $state = self::sessionState();
        $sessionEngineFactory = static fn(): object => (object)['engine' => 'session'];
        $received = [];

        $session = (new application_session_service_creator())->createSessionService(
            $container,
            $state,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'session'];
            },
            $sessionEngineFactory,
            'profile',
            'custom'
        );

        $this->assertSame('session', $session->service);
        $this->assertSame('\\' . session::class, $received[0] ?? null);
        $this->assertSame('profile', $received[1] ?? null);
        $this->assertSame('custom', $received[2] ?? null);
        $this->assertSame($container->get('config')->databaseConfig, $received[3] ?? null);
        $this->assertSame($container->get('request_input'), $received[4] ?? null);
        $this->assertSame($container->get('error'), ($received[5])());
        $this->assertSame($container->get('request'), $received[6] ?? null);
        $this->assertNull($received[7] ?? null);
        $this->assertSame('nested', ($received[8])('nested', 'group')->namespace);
        $this->assertSame('2026-06-02', ($received[9])('2026-06-02')->date);
        $this->assertSame('/app', ($received[10])('/app', 'example.test')->path);
        $this->assertSame($container->get('pear_http_session_loader'), $received[11] ?? null);
        $this->assertSame($sessionEngineFactory, $received[12] ?? null);
        $this->assertSame($state, $received[13] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[14] ?? null);
        $this->assertSame($container->get('config'), $received[15] ?? null);
        $this->assertSame('cache-key', ($received[16])('cache-key')->type);
        $this->assertSame($container->get('php_runtime_settings'), $received[17] ?? null);
        $this->assertSame($container->get('native_session'), $received[18] ?? null);
        $this->assertSame('reader-value', ($received[19])(['key' => 'reader-value'], 'key'));
    }

    public function testSessionCreatorResolvesDefaultApplicationNamespace(): void
    {
        $container = $this->containerWithSessionDependencies();
        $state = self::sessionState();
        $received = [];

        (new application_session_service_creator())->createSessionService(
            $container,
            $state,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'session'];
            },
            static fn(): object => (object)['engine' => 'session']
        );

        $this->assertSame('shop-replaced', $received[1] ?? null);
        $this->assertSame('app', $received[2] ?? null);
    }

    public function testProjectServiceClassAvailabilityCheckIsInjected(): void
    {
        $checkedClasses = [];
        $creator = new application_session_service_creator(
            null,
            static function (string $className) use (&$checkedClasses): bool {
                $checkedClasses[] = $className;

                return false;
            }
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service "session" does not expose a project class.');

        try {
            $creator->createSessionService(
                $this->containerWithSessionDependencies(),
                self::sessionState(),
                static fn(): object => new stdClass(),
                static fn(): object => new stdClass(),
                'profile',
                'custom'
            );
        } finally {
            $this->assertSame(['\fan\project\service\session'], $checkedClasses);
        }
    }

    public function testNullGroupUsesInjectedFatalExceptionFactory(): void
    {
        $calls = [];
        $creator = new application_session_service_creator(
            static function (string $message, ?object $requestInput = null, ?object $exceptionHeaderWriter = null) use (&$calls): Throwable {
                $calls[] = [$message, $requestInput, $exceptionHeaderWriter];

                return new RuntimeException('fatal: ' . $message);
            }
        );
        $container = $this->containerWithSessionDependencies();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('fatal: Unset group name for \fan\core\service\session.');

        try {
            $creator->createSessionService(
                $container,
                self::sessionState(),
                static fn(): object => new stdClass(),
                static fn(): object => new stdClass(),
                'profile',
                null
            );
        } finally {
            $this->assertSame('Unset group name for \fan\core\service\session.', $calls[0][0]);
            $this->assertSame($container->get('request_input'), $calls[0][1]);
            $this->assertSame($container->get('header_writer'), $calls[0][2]);
        }
    }

    public function testNullGroupWithoutFatalFactoryKeepsConfigurationError(): void
    {
        $creator = new application_session_service_creator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Fatal exception factory is not configured for session service creator.');

        $creator->createSessionService(
            $this->containerWithSessionDependencies(),
            self::sessionState(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            'profile',
            null
        );
    }

    private function containerWithSessionDependencies(): container
    {
        $container = new container();
        $container
            ->factory('config', static fn(): ApplicationSessionConfigDouble => new ApplicationSessionConfigDouble())
            ->factory('application', static fn(): object => new class {
                public function getAppName(): string
                {
                    return 'shop';
                }
            })
            ->factory('request_input', static fn(): object => (object)['name' => 'request-input'])
            ->factory('header_writer', static fn(): object => (object)['name' => 'header-writer'])
            ->factory('error', static fn(): object => (object)['name' => 'error'])
            ->factory('request', static fn(): object => (object)['name' => 'request'])
            ->factory('session', static fn(container $container, string $namespace, string $group): object => (object)['namespace' => $namespace, 'group' => $group], false)
            ->factory('date', static fn(container $container, string $date): object => (object)['date' => $date], false)
            ->factory('cookie', static fn(container $container, mixed $path, mixed $domain): object => (object)['path' => $path, 'domain' => $domain], false)
            ->factory('pear_http_session_loader', static fn(): object => (object)['name' => 'pear-session'])
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('php_runtime_settings', static fn(): object => (object)['name' => 'php-runtime'])
            ->factory('native_session', static fn(): object => (object)['name' => 'native-session'])
            ->factory('array_value_reader', static fn(): callable => static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default);

        return $container;
    }

    private static function sessionState(): session_state
    {
        return new session_state(
            static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default
        );
    }
}

final class ApplicationSessionConfigDouble
{
    public object $databaseConfig;
    private ApplicationSessionConfigRowDouble $sessionConfig;

    public function __construct()
    {
        $this->databaseConfig = (object)['type' => 'database-config'];
        $this->sessionConfig = new ApplicationSessionConfigRowDouble();
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return match ($key) {
            'database' => $this->databaseConfig,
            'session' => $this->sessionConfig,
            default => $default,
        };
    }
}

final class ApplicationSessionConfigRowDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $key === ['REPLACE_APP', 'shop'] ? 'shop-replaced' : $default;
    }
}

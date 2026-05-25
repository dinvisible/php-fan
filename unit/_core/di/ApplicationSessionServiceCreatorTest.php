<?php

declare(strict_types=1);

use fan\core\di\application_session_service_creator;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;
use fan\core\service\session_state;
use fan\project\service\session;


final class ApplicationSessionServiceCreatorTest extends TestCase
{    public function testSessionCreatorPassesExplicitDependenciesToInjectedFactory(): void
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

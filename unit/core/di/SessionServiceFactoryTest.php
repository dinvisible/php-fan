<?php

declare(strict_types=1);

use fan\core\di\session_service_factory;
use fan\core\service\session;
use fan\core\service\session_state;
use PHPUnit\Framework\TestCase;

if (!function_exists('get_class_name')) {
    function get_class_name(string|object $object): ?string
    {
        if (is_object($object)) {
            $object = get_class($object);
        }

        $parts = explode('\\', $object);

        return end($parts);
    }
}

final class SessionServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreSessionServiceWithTypedConstructor(): void
    {
        $overrideCalls = [];
        $factory = new session_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        );
        $databaseConfig = new stdClass();
        $requestInput = new stdClass();
        $errorFactory = static fn(): object => new stdClass();
        $requestService = new stdClass();
        $logService = new stdClass();
        $sessionFactory = static fn(string $namespace, string $group): object => (object)['namespace' => $namespace, 'group' => $group];
        $dateFactory = static fn(string $date): object => (object)['date' => $date];
        $cookieFactory = static fn(mixed $path, mixed $domain): object => (object)['path' => $path, 'domain' => $domain];
        $pearSessionSupportLoader = new stdClass();
        $sessionEngineFactory = static fn(): object => new stdClass();
        $state = self::sessionState();
        $runtime = new SessionServiceFactoryRuntimeDouble();
        $configurator = new SessionServiceFactoryConfiguratorDouble(new SessionServiceFactoryConfigDouble([
            'ENABLED' => false,
        ]));
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $settings = new SessionServiceFactoryRuntimeSettingsDouble();
        $nativeSession = new stdClass();
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;

        $session = $factory(
            session::class,
            'profile',
            'custom',
            $databaseConfig,
            $requestInput,
            $errorFactory,
            $requestService,
            $logService,
            $sessionFactory,
            $dateFactory,
            $cookieFactory,
            $pearSessionSupportLoader,
            $sessionEngineFactory,
            $state,
            $runtime,
            $configurator,
            $cacheFactory,
            $settings,
            $nativeSession,
            $arrayValueReader
        );

        $this->assertInstanceOf(session::class, $session);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([session::class], $runtime->initializer->serviceParams);
        $this->assertSame([$session], $configurator->getServiceConfigCalls);
        $this->assertSame([['session', 'ENABLED']], $configurator->resetCalls);
        $this->assertSame($session, $state->getInstance('custom', 'profile'));
    }

    public function testFactoryDelegatesConfiguredSessionServiceOverrides(): void
    {
        $calls = [];
        $factory = new session_service_factory(
            static function (string $className, array $arguments) use (&$calls): object {
                $calls[] = [$className, $arguments];

                return new SessionServiceFactoryProbe(...$arguments);
            }
        );
        $databaseConfig = new stdClass();
        $requestInput = new stdClass();
        $errorFactory = static fn(): object => new stdClass();
        $requestService = new stdClass();
        $logService = new stdClass();
        $sessionFactory = static fn(string $namespace, string $group): object => (object)['namespace' => $namespace, 'group' => $group];
        $dateFactory = static fn(string $date): object => (object)['date' => $date];
        $cookieFactory = static fn(mixed $path, mixed $domain): object => (object)['path' => $path, 'domain' => $domain];
        $pearSessionSupportLoader = new stdClass();
        $sessionEngineFactory = static fn(): object => new stdClass();
        $state = new stdClass();
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $settings = new stdClass();
        $nativeSession = new stdClass();
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;

        $session = $factory(
            SessionServiceFactoryProbe::class,
            'profile',
            'custom',
            $databaseConfig,
            $requestInput,
            $errorFactory,
            $requestService,
            $logService,
            $sessionFactory,
            $dateFactory,
            $cookieFactory,
            $pearSessionSupportLoader,
            $sessionEngineFactory,
            $state,
            $runtime,
            $configurator,
            $cacheFactory,
            $settings,
            $nativeSession,
            $arrayValueReader
        );

        $this->assertInstanceOf(SessionServiceFactoryProbe::class, $session);
        $this->assertSame(SessionServiceFactoryProbe::class, $calls[0][0]);
        $this->assertSame([
            'profile',
            'custom',
            $databaseConfig,
            $requestInput,
            $errorFactory,
            $requestService,
            $logService,
            $sessionFactory,
            $dateFactory,
            $cookieFactory,
            $pearSessionSupportLoader,
            $sessionEngineFactory,
            $state,
            $runtime,
            $configurator,
            $cacheFactory,
            $settings,
            $nativeSession,
            $arrayValueReader,
        ], $calls[0][1]);
        $this->assertSame('profile', $session->nameSpace);
        $this->assertSame('custom', $session->group);
        $this->assertSame($databaseConfig, $session->databaseConfig);
        $this->assertSame($requestInput, $session->requestInput);
        $this->assertSame($errorFactory, $session->errorFactory);
        $this->assertSame($requestService, $session->requestService);
        $this->assertSame($logService, $session->logService);
        $this->assertSame($sessionFactory, $session->sessionFactory);
        $this->assertSame($dateFactory, $session->dateFactory);
        $this->assertSame($cookieFactory, $session->cookieFactory);
        $this->assertSame($pearSessionSupportLoader, $session->pearSessionSupportLoader);
        $this->assertSame($sessionEngineFactory, $session->sessionEngineFactory);
        $this->assertSame($state, $session->state);
        $this->assertSame($runtime, $session->serviceBootstrapRuntime);
        $this->assertSame($configurator, $session->serviceConfigurator);
        $this->assertSame($cacheFactory, $session->serviceCacheFactory);
        $this->assertSame($settings, $session->phpRuntimeSettings);
        $this->assertSame($nativeSession, $session->nativeSession);
        $this->assertSame($arrayValueReader, $session->arrayValueReader);
    }

                private static function invokeFactoryWithMinimalArguments(session_service_factory $factory): void
    {
        $factory(
            session::class,
            'profile',
            'custom',
            new stdClass(),
            new stdClass(),
            static fn(): object => new stdClass(),
            new stdClass(),
            new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            new stdClass(),
            static fn(): object => new stdClass(),
            self::sessionState(),
            new stdClass(),
            new stdClass(),
            static fn(): object => new stdClass(),
            new stdClass(),
            new stdClass()
        );
    }

    private static function sessionState(): session_state
    {
        return new session_state(
            static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default
        );
    }
}

final class SessionServiceFactoryProbe
{
    public function __construct(
        public string $nameSpace,
        public string $group,
        public ?object $databaseConfig,
        public ?object $requestInput,
        public $errorFactory,
        public ?object $requestService,
        public ?object $logService,
        public $sessionFactory,
        public $dateFactory,
        public $cookieFactory,
        public ?object $pearSessionSupportLoader,
        public $sessionEngineFactory,
        public object $state,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory,
        public object $phpRuntimeSettings,
        public object $nativeSession,
        public $arrayValueReader
    ) {
    }
}


final class SessionServiceFactoryRuntimeDouble
{
    public SessionServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new SessionServiceFactoryInitializerDouble();
    }

    public function getInitializer(): SessionServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    public function classNameResolver(): callable
    {
        return static function (object $object): string {
            $parts = explode('\\', get_class($object));

            return end($parts);
        };
    }

    public function arrayValueReader(): callable
    {
        return static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
    }
}

final class SessionServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class SessionServiceFactoryConfiguratorDouble
{
    public array $getServiceConfigCalls = [];
    public array $resetCalls = [];

    public function __construct(private object $config)
    {
    }

    public function getServiceConfig(object $service): object
    {
        $this->getServiceConfigCalls[] = $service;

        return $this->config;
    }

    public function reset(string $className, string $key): void
    {
        $this->resetCalls[] = [$className, $key];
    }
}

final class SessionServiceFactoryConfigDouble extends ArrayObject
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this;
        }

        return array_key_exists($key, $this->getArrayCopy()) ? $this[$key] : $default;
    }
}

final class SessionServiceFactoryRuntimeSettingsDouble
{
    public array $sets = [];

    public function set(string $key, mixed $value): void
    {
        $this->sets[] = [$key, $value];
    }
}

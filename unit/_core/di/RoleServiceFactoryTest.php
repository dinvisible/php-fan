<?php

declare(strict_types=1);

use fan\core\di\role_service_factory;
use fan\core\service\role;
use PHPUnit\Framework\TestCase;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;

final class RoleServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreRoleServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $currentUser = new RoleServiceFactoryCurrentUserDouble(['admin' => null]);
        $session = new RoleServiceFactorySessionDouble([
            role::COMMON_KEY => ['common' => null],
            'factory-space' => ['member' => null],
        ]);
        $currentUserFactory = static function (bool $checkLogout) use ($currentUser): RoleServiceFactoryCurrentUserDouble {
            $currentUser->checkLogoutValues[] = $checkLogout;

            return $currentUser;
        };
        $sessionFactory = static fn(string $namespace, string $group): RoleServiceFactorySessionDouble => $session;
        $errorLogger = new RoleServiceFactoryErrorLoggerDouble();
        $userSpaceProvider = static fn(): string => 'factory-space';
        $dateFactory = static fn(string $date, mixed $format = null): object => (object)['date' => $date, 'format' => $format];
        $runtime = new RoleServiceFactoryRuntimeDouble();
        $config = new RoleServiceFactoryConfigDouble(['CHECK_LOGOUT' => false]);
        $configurator = new RoleServiceFactoryConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $cacheFactory = static function (string $type) use (&$cacheFactoryCalls): object {
            $cacheFactoryCalls[] = $type;

            return (object)['type' => $type];
        };
        $overrideCalls = [];

        $service = (new role_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            role::class,
            $currentUserFactory,
            $sessionFactory,
            $errorLogger,
            $userSpaceProvider,
            $dateFactory,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(role::class, $service);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([role::class], $runtime->initializer->serviceParams);
        $this->assertSame([$service], $configurator->getServiceConfigCalls);
        $this->assertSame([
            ['role', 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame([false], $currentUser->checkLogoutValues);
        $this->assertSame(['common', 'member', 'admin'], $service->getRoles());
        $this->assertSame([
            ['role', 'system'],
            ['role', 'system'],
        ], $session->lastGetByLink);
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $currentUserFactory = static fn(bool $checkLogout): object => (object)['checkLogout' => $checkLogout];
        $sessionFactory = static fn(string $namespace, string $group): object => (object)['namespace' => $namespace, 'group' => $group];
        $errorLogger = new stdClass();
        $userSpaceProvider = static fn(): string => 'default-space';
        $dateFactory = static fn(string $date, mixed $format = null): object => (object)['date' => $date, 'format' => $format];
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $configuredCalls = [];

        $role = (new role_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            RoleServiceFactoryProbe::class,
            $currentUserFactory,
            $sessionFactory,
            $errorLogger,
            $userSpaceProvider,
            $dateFactory,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(RoleServiceFactoryProbe::class, $role);
        $this->assertSame($currentUserFactory, $role->currentUserFactory);
        $this->assertSame($sessionFactory, $role->sessionFactory);
        $this->assertSame($errorLogger, $role->errorLogger);
        $this->assertSame($userSpaceProvider, $role->userSpaceProvider);
        $this->assertSame($dateFactory, $role->dateFactory);
        $this->assertSame($runtime, $role->serviceBootstrapRuntime);
        $this->assertSame($configurator, $role->serviceConfigurator);
        $this->assertSame($cacheFactory, $role->serviceCacheFactory);
        $this->assertSame(RoleServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([
            $currentUserFactory,
            $sessionFactory,
            $errorLogger,
            $userSpaceProvider,
            $dateFactory,
            $runtime,
            $configurator,
            $cacheFactory,
        ], $configuredCalls[0][1] ?? null);
    }

                private function ensureBaseFunctionAliases(): void
    {
        if (!function_exists('get_class_name')) {
            eval('function get_class_name(string|object $object): ?string { if (is_object($object)) { $object = get_class($object); } $parts = explode(chr(92), $object); return end($parts); }');
        }
        if (!function_exists('fan\core\base\get_class_name')) {
            eval('namespace fan\core\base { function get_class_name(string|object $object): ?string { return \get_class_name($object); } }');
        }
    }
}

final class RoleServiceFactoryProbe
{
    public function __construct(
        public $currentUserFactory,
        public $sessionFactory,
        public object $errorLogger,
        public $userSpaceProvider,
        public $dateFactory,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory
    ) {
    }
}


final class RoleServiceFactoryRuntimeDouble
{
    public RoleServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new RoleServiceFactoryInitializerDouble();
    }

    public function getInitializer(): RoleServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    private ?service_listener_state $baseServiceListenerState = null;

    private ?service_single_state $baseServiceSingleState = null;

    public function serviceListenerState(): service_listener_state
    {
        return $this->baseServiceListenerState ??= new service_listener_state();
    }

    public function serviceSingleState(): service_single_state
    {
        return $this->baseServiceSingleState ??= new service_single_state();
    }

    public function classNameResolver(): callable
    {
        return static fn(object|string $object): string => get_class_name($object) ?? (is_object($object) ? get_class($object) : $object);
    }
}

final class RoleServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class RoleServiceFactoryConfiguratorDouble
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

    public function reset(string $className, mixed $key): void
    {
        $this->resetCalls[] = [$className, $key];
    }
}

final class RoleServiceFactoryConfigDouble
{
    public function __construct(private array $data)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}

final class RoleServiceFactoryCurrentUserDouble
{
    public array $checkLogoutValues = [];

    public function __construct(private array $roles)
    {
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function removeRole(array $roles): void
    {
    }
}

final class RoleServiceFactorySessionDouble
{
    public array $lastGetByLink = [];
    private array $links;

    public function __construct(array $sessionRoles = [], array $fixQttRoles = [])
    {
        $this->links = [
            'session' => $sessionRoles,
            'fix_qtt_roles' => $fixQttRoles,
        ];
    }

    public function &getByLink(string $key, array $default = []): array
    {
        $this->lastGetByLink[] = ['role', 'system'];
        if (!array_key_exists($key, $this->links)) {
            $this->links[$key] = $default;
        }

        return $this->links[$key];
    }
}

final class RoleServiceFactoryErrorLoggerDouble
{
    public array $messages = [];

    public function logErrorMessage($message, $title = '', $note = '', $fixPosition = false): void
    {
        $this->messages[] = [$message, $title];
    }
}

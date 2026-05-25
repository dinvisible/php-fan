<?php

declare(strict_types=1);

use fan\core\di\user_service_factory;
use fan\core\service\user;
use fan\core\service\user_state;
use PHPUnit\Framework\TestCase;
use FanTest\_core\ConfigRowFactory;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\core\service\user\base;


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

final class UserServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreUserServiceWithTypedConstructor(): void
    {
        $this->ensureBaseDataHelpers();
        $overrideCalls = [];
        $factory = new user_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        );
        $engineFactoryCalls = [];
        $config = new UserServiceFactoryConfigDouble([
            'space' => [
                'main' => ConfigRowFactory::row([
                    'APPLICATIONS' => ['crm'],
                    'ENGINE' => 'factory_engine',
                ]),
            ],
        ]);
        $configFactory = static fn(): object => new stdClass();
        $sessionFactory = static fn(string $namespace, string $group): object => (object)['namespace' => $namespace, 'group' => $group];
        $currentUserFactory = static fn(): object => new stdClass();
        $applicationFactory = static fn(): object => new stdClass();
        $errorFactory = static fn(): object => new stdClass();
        $requestInputFactory = static fn(): object => new stdClass();
        $entityFactory = static fn(): object => new stdClass();
        $userEngineFactory = static function (string $engineClass, mixed $identifier) use (&$engineFactoryCalls): object {
            $engineFactoryCalls[] = [$engineClass, $identifier];

            return new UserServiceFactoryEngineDouble($identifier);
        };
        $runtime = new UserServiceFactoryRuntimeDouble();
        $configurator = new UserServiceFactoryConfiguratorDouble($config);
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $state = new user_state();
        $instanceKeyEncoder = static fn(mixed $identifier): string => 'key:' . (string)$identifier;
        $snapshotEncoder = static fn(mixed $state): string => 'snapshot';
        $snapshotDecoder = static fn(string $payload, mixed $default = null): mixed => $default;
        $arrayAdducer = static fn(mixed $value): array => is_array($value) ? $value : [$value];

        $user = $factory(
            user::class,
            'tester',
            'main',
            $configFactory,
            $sessionFactory,
            $currentUserFactory,
            $applicationFactory,
            $errorFactory,
            $requestInputFactory,
            $entityFactory,
            $userEngineFactory,
            $runtime,
            $configurator,
            $cacheFactory,
            $state,
            $instanceKeyEncoder,
            $snapshotEncoder,
            $snapshotDecoder,
            $arrayAdducer
        );

        $this->assertInstanceOf(user::class, $user);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([user::class], $runtime->initializer->serviceParams);
        $this->assertSame([['fan\\project\\service\\user\\factory_engine', true]], $runtime->loadClassCalls);
        $this->assertSame([$user], $configurator->getServiceConfigCalls);
        $this->assertSame([[user::class, 'ENABLED']], $configurator->resetCalls);
        $this->assertSame([['\\fan\\project\\service\\user\\factory_engine', 'tester']], $engineFactoryCalls);
        $this->assertSame($user, $state->getInstance('main', 'tester'));
        $this->assertSame('main', $user->getUserSpace());
        $this->assertSame('tester', $user->getEngine()?->getAllData()['id']);
        $this->assertSame(['member' => null], $user->getRoles(true));
        $this->assertSame($user, $user->getEngine()?->facadeObject());
    }

    public function testFactoryDelegatesConfiguredUserServiceOverrides(): void
    {
        $calls = [];
        $factory = new user_service_factory(
            static function (string $className, array $arguments) use (&$calls): object {
                $calls[] = [$className, $arguments];

                return new UserServiceFactoryProbe(...$arguments);
            }
        );
        $configFactory = static fn(): object => new stdClass();
        $sessionFactory = static fn(string $namespace, string $group): object => (object)['namespace' => $namespace, 'group' => $group];
        $currentUserFactory = static fn(): object => new stdClass();
        $applicationFactory = static fn(): object => new stdClass();
        $errorFactory = static fn(): object => new stdClass();
        $requestInputFactory = static fn(): object => new stdClass();
        $entityFactory = static fn(): object => new stdClass();
        $userEngineFactory = static fn(string $engineClass, mixed $identifier): object => (object)['engine' => $engineClass, 'identifier' => $identifier];
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $state = new stdClass();
        $instanceKeyEncoder = static fn(mixed $identifier): string => 'key:' . (string)$identifier;
        $snapshotEncoder = static fn(mixed $state): string => 'snapshot';
        $snapshotDecoder = static fn(string $payload, mixed $default = null): mixed => $default;
        $arrayAdducer = static fn(mixed $value): array => is_array($value) ? $value : [$value];

        $user = $factory(
            UserServiceFactoryProbe::class,
            'tester',
            'main',
            $configFactory,
            $sessionFactory,
            $currentUserFactory,
            $applicationFactory,
            $errorFactory,
            $requestInputFactory,
            $entityFactory,
            $userEngineFactory,
            $runtime,
            $configurator,
            $cacheFactory,
            $state,
            $instanceKeyEncoder,
            $snapshotEncoder,
            $snapshotDecoder,
            $arrayAdducer
        );

        $this->assertInstanceOf(UserServiceFactoryProbe::class, $user);
        $this->assertSame(UserServiceFactoryProbe::class, $calls[0][0]);
        $this->assertSame([
            'tester',
            'main',
            $configFactory,
            $sessionFactory,
            $currentUserFactory,
            $applicationFactory,
            $errorFactory,
            $requestInputFactory,
            $entityFactory,
            $userEngineFactory,
            $runtime,
            $configurator,
            $cacheFactory,
            $state,
            $instanceKeyEncoder,
            $snapshotEncoder,
            $snapshotDecoder,
            $arrayAdducer,
        ], $calls[0][1]);
        $this->assertSame('tester', $user->identifyer);
        $this->assertSame('main', $user->userSpace);
        $this->assertSame($configFactory, $user->configFactory);
        $this->assertSame($sessionFactory, $user->sessionFactory);
        $this->assertSame($currentUserFactory, $user->currentUserFactory);
        $this->assertSame($applicationFactory, $user->applicationFactory);
        $this->assertSame($errorFactory, $user->errorFactory);
        $this->assertSame($requestInputFactory, $user->requestInputFactory);
        $this->assertSame($entityFactory, $user->entityFactory);
        $this->assertSame($userEngineFactory, $user->userEngineFactory);
        $this->assertSame($runtime, $user->serviceBootstrapRuntime);
        $this->assertSame($configurator, $user->serviceConfigurator);
        $this->assertSame($cacheFactory, $user->serviceCacheFactory);
        $this->assertSame($state, $user->userState);
        $this->assertSame($instanceKeyEncoder, $user->instanceKeyEncoder);
        $this->assertSame($snapshotEncoder, $user->snapshotEncoder);
        $this->assertSame($snapshotDecoder, $user->snapshotDecoder);
        $this->assertSame($arrayAdducer, $user->arrayAdducer);
    }

                private static function invokeFactoryWithMinimalArguments(user_service_factory $factory): void
    {
        $factory(
            user::class,
            'tester',
            'main',
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            new stdClass(),
            new stdClass(),
            static fn(): object => new stdClass(),
            new user_state(),
            static fn(mixed $identifier): string => 'key:' . (string)$identifier,
            static fn(mixed $state): string => 'snapshot',
            static fn(string $payload, mixed $default = null): mixed => $default
        );
    }

    private function ensureBaseDataHelpers(): void
    {
        if (!function_exists('fan\core\base\get_class_alt')) {
            eval('namespace fan\core\base { function get_class_alt(mixed $value): string { return is_object($value) ? get_class($value) : (string)$value; } }');
        }
        if (!function_exists('fan\core\base\get_class_name')) {
            eval('namespace fan\core\base { function get_class_name(string|object $object): ?string { return \get_class_name($object); } }');
        }
    }
}

final class UserServiceFactoryProbe
{
    public function __construct(
        public mixed $identifyer,
        public string $userSpace,
        public $configFactory,
        public $sessionFactory,
        public $currentUserFactory,
        public $applicationFactory,
        public $errorFactory,
        public $requestInputFactory,
        public $entityFactory,
        public $userEngineFactory,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory,
        public object $userState,
        public $instanceKeyEncoder,
        public $snapshotEncoder,
        public $snapshotDecoder,
        public $arrayAdducer
    ) {
    }
}


final class UserServiceFactoryEngineDouble extends base
{
    public function makePasswordHash(string $password): string
    {
        return 'hash:' . $password;
    }

    public function facadeObject(): ?object
    {
        return $this->facade;
    }

    protected function _loadData(): bool
    {
        $this->isValid = true;
        $this->isNew = false;
        $this->data = [
            'id' => $this->identifyer,
            'roles' => ['member' => null],
        ];

        return true;
    }

    protected function _saveData(): bool
    {
        return false;
    }

    protected function _validateForSave(): bool
    {
        return false;
    }
}

final class UserServiceFactoryConfigDouble
{
    public function __construct(private array $data)
    {
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        if (is_array($key)) {
            $value = $this->data;
            foreach ($key as $part) {
                if (!is_array($value) || !array_key_exists($part, $value)) {
                    return $default;
                }
                $value = $value[$part];
            }

            return $value;
        }

        return $key === null ? $this->data : ($this->data[$key] ?? $default);
    }
}

final class UserServiceFactoryRuntimeDouble
{
    public UserServiceFactoryInitializerDouble $initializer;
    public array $loadClassCalls = [];

    public function __construct()
    {
        $this->initializer = new UserServiceFactoryInitializerDouble();
    }

    public function getInitializer(): UserServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    public function loadClass(string $class, bool $makeAlias = true): bool
    {
        $this->loadClassCalls[] = [$class, $makeAlias];

        return true;
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
        return static fn(object $object): string => get_class($object);
    }
}

final class UserServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class UserServiceFactoryConfiguratorDouble
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

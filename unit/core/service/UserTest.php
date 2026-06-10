<?php

declare(strict_types=1);

namespace fan\core\base {
    if (!function_exists(__NAMESPACE__ . '\\get_class_alt')) {
        function get_class_alt(mixed $value): string
        {
            return is_object($value) ? get_class($value) : (string)$value;
        }
    }

    if (!function_exists(__NAMESPACE__ . '\\get_class_name')) {
        function get_class_name(string|object $object): ?string
        {
            $class = is_object($object) ? get_class($object) : $object;
            $parts = explode('\\', $class);

            return array_pop($parts);
        }
    }
}

namespace ServiceUserConstructorProbe {
    final class engine_double extends \fan\core\service\user\base
    {
        public function makePasswordHash(string $password): string
        {
            return 'hash:' . $password;
        }

        public function facadeObject(): ?object
        {
            return $this->facade;
        }

        public function configObject(): ?object
        {
            return $this->config;
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
}

namespace {
use fan\core\service\user;
use fan\core\service\user_state;
use FanTest\core\SourceFileContractTestCase;

class GeneratedPendingServiceUserTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/user.php';

    public function testSetCurrentPersistsThroughInjectedSessionFactory(): void
    {
        $session = new ServiceUserSessionDouble(['currents' => [], 'priority' => []]);
        $application = new ServiceUserApplicationDouble('other-app');
        $state = new user_state();
        $user = $this->userService(new ServiceUserDataDouble(true), ['expected-app'], $state);

        $user->setUserDependencies(
            null,
            fn(string $namespace, string $group): ServiceUserSessionDouble => $session->for($namespace, $group),
            null,
            fn(): ServiceUserApplicationDouble => $application
        );

        $this->assertTrue($user->setCurrent());
        $this->assertSame(user::SES_NAMESPACE, $session->namespace);
        $this->assertSame('system', $session->group);
        $this->assertSame($user, $session->data['currents']['main']);
    }

    public function testSetPrioritySpacePersistsThroughInjectedSessionFactory(): void
    {
        $session = new ServiceUserSessionDouble(['currents' => [], 'priority' => []]);
        $state = new user_state();
        $user = $this->userService(new ServiceUserDataDouble(true), ['crm'], $state);

        $user->setUserDependencies(
            null,
            fn(string $namespace, string $group): ServiceUserSessionDouble => $session->for($namespace, $group)
        );

        $this->assertTrue($user->setPrioritySpace('crm'));
        $this->assertSame(['crm' => 'main'], $session->data['priority']);
    }

    public function testLogoutUsesInjectedSessionAndCurrentUserFactory(): void
    {
        $session = new ServiceUserSessionDouble(['currents' => [], 'priority' => []]);
        $data = new ServiceUserDataDouble(true);
        $state = new user_state();
        $user = $this->userService($data, ['crm'], $state);
        $state->setCurrentUsers(['main' => $user]);

        $currentUserCalls = 0;
        $user->setUserDependencies(
            null,
            fn(string $namespace, string $group): ServiceUserSessionDouble => $session->for($namespace, $group),
            function () use (&$currentUserCalls): null {
                $currentUserCalls++;
                return null;
            },
            fn(): ServiceUserApplicationDouble => new ServiceUserApplicationDouble('crm')
        );

        $this->assertTrue($user->logout());
        $this->assertSame([], $session->data['currents']);
        $this->assertTrue($data->logoutCalled);
        $this->assertSame(1, $currentUserCalls);
    }

    public function testOnSetAppNameRefreshesCurrentUserThroughInjectedFactory(): void
    {
        $data = new ServiceUserDataDouble(true);
        $state = new user_state();
        $user = $this->userService($data, ['crm'], $state);
        $state->setCurrentUserSpace('main');

        $currentUserCalls = 0;
        $user->setUserDependencies(
            null,
            null,
            function () use (&$currentUserCalls): null {
                $currentUserCalls++;
                return null;
            }
        );

        $user->onSetAppName('other-app');

        $this->assertSame(1, $currentUserCalls);
        $this->assertNull($state->getCurrentUserSpace());
    }

    public function testSetCurrentRequiresInjectedSessionFactory(): void
    {
        $user = $this->userService(new ServiceUserDataDouble(true), ['crm'], new user_state());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Session service is not configured for user service.');

        $user->setCurrent();
    }

    public function testLogoutRequiresInjectedCurrentUserFactoryWhenNoCurrentUserRemains(): void
    {
        $session = new ServiceUserSessionDouble(['currents' => [], 'priority' => []]);
        $data = new ServiceUserDataDouble(true);
        $state = new user_state();
        $user = $this->userService($data, ['crm'], $state);
        $state->setCurrentUsers(['main' => $user]);

        $user->setUserDependencies(
            null,
            fn(string $namespace, string $group): ServiceUserSessionDouble => $session->for($namespace, $group),
            null,
            fn(): ServiceUserApplicationDouble => new ServiceUserApplicationDouble('crm')
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Current user factory is not configured for user service.');

        $user->logout();
    }

    public function testRemoveRoleUsesInjectedArrayAdducer(): void
    {
        $data = new ServiceUserDataDouble(true, [
            'admin' => null,
            'member' => null,
        ]);
        $state = new user_state();
        $user = $this->userService($data, ['crm'], $state);
        $state->setCurrentUsers([]);
        $arrayAdducerCalls = [];

        $user->setUserDependencies(
            arrayAdducer: static function (mixed $value) use (&$arrayAdducerCalls): array {
                $arrayAdducerCalls[] = $value;

                return is_array($value) ? $value : [$value];
            }
        );

        $this->assertSame($user, $user->removeRole('admin'));
        $this->assertSame(['admin'], $arrayAdducerCalls);
        $this->assertSame(['member' => null], $data->roles);
    }

    public function testRemoveRoleRequiresInjectedArrayAdducer(): void
    {
        $user = (new ReflectionClass(user::class))->newInstanceWithoutConstructor();
        $this->setProperty($user, user::class, 'userData', new ServiceUserDataDouble(true, ['member' => null]));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Array adducer is not configured for user service.');

        $user->removeRole('member');
    }

    public function testSetPrioritySpaceRequiresInjectedApplicationFactoryWhenAppNameIsOmitted(): void
    {
        $session = new ServiceUserSessionDouble(['currents' => [], 'priority' => []]);
        $user = $this->userService(new ServiceUserDataDouble(true), ['crm'], new user_state());

        $user->setUserDependencies(
            null,
            fn(string $namespace, string $group): ServiceUserSessionDouble => $session->for($namespace, $group)
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Application service is not configured for user service.');

        $user->setPrioritySpace();
    }

    public function testStateAccessRequiresInjectedUserState(): void
    {
        $user = (new ReflectionClass(user::class))->newInstanceWithoutConstructor();
        $this->setProperty($user, user::class, 'userSpace', 'main');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('User state is not configured for user service.');

        $user->isCurrent();
    }

    public function testSourceUsesInjectedFactoriesInsteadOfContainerLookup(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('setUserDependencies', $source);
        $this->assertStringContainsString('$this->userData = $this->createUserEngine($engine, $identifyer);', $source);
        $this->assertStringContainsString('return ($this->instanceKeyEncoder())($identifyer);', $source);
        $this->assertStringContainsString('($this->arrayAdducer())($role)', $source);
        $this->assertStringNotContainsString('parent::__construct();', $source);
        $this->assertStringNotContainsString('getContainerService(', $source);
        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('self::$', $source);
        $this->assertStringNotContainsString('private static array $instances', $source);
        $this->assertStringNotContainsString('new $engine($identifyer)', $source);
        $this->assertStringNotContainsString('new user_state()', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
        $this->assertStringNotContainsString('safe_serializer::stableKey', $source);
        $this->assertStringNotContainsString('safe_serializer::', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $runtime = new ServiceUserRuntimeDouble();
        $config = $this->baseServiceConfig();
        $configurator = new ServiceUserConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $engineFactoryCalls = [];

        $user = new ServiceUserConstructorProbe(
            'tester',
            'main',
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            function (string $engineClass, mixed $identifyer) use (&$engineFactoryCalls): \fan\core\service\user\base {
                $engineFactoryCalls[] = [$engineClass, $identifyer];

                return new $engineClass($identifyer);
            },
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            },
            new user_state(),
            static fn(mixed $identifyer): string => 'key:' . (string)$identifyer
        );

        $this->assertSame([ServiceUserConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$user], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceUserConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame('main', $user->getUserSpace());
        $this->assertSame('tester', $user->getEngine()->getAllData()['id']);
        $this->assertSame(['member' => null], $user->getRoles(true));
        $this->assertSame([
            ['\\ServiceUserConstructorProbe\\engine_double', 'tester'],
        ], $engineFactoryCalls);
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testConstructorRequiresInjectedUserState(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('User state is not configured for user service.');

        new ServiceUserConstructorProbe(
            'tester',
            'main',
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null
        );
    }

    public function testUsesInjectedInstanceKeyEncoderForNonScalarIdentifiers(): void
    {
        $state = new user_state();
        $encoded = [];
        $user = $this->userService(new ServiceUserDataDouble(true), ['crm'], $state);
        $this->setProperty($user, user::class, 'identifyer', ['id' => 7]);

        $user->setUserDependencies(
            userState: $state,
            instanceKeyEncoder: static function (mixed $identifyer) use (&$encoded): string {
                $encoded[] = $identifyer;

                return 'encoded-user-key';
            }
        );

        $this->assertSame($user, $state->getInstance('main', 'encoded-user-key'));
        $this->assertSame([[ 'id' => 7 ]], $encoded);
    }

    public function testMissingInstanceKeyEncoderFailsAtUseTime(): void
    {
        $state = new user_state();
        $user = $this->userService(new ServiceUserDataDouble(true), ['crm'], $state);
        $this->setProperty($user, user::class, 'identifyer', ['id' => 7]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('User instance key encoder is not configured for user service.');

        $user->setUserDependencies(userState: $state);
    }

    public function testUnserializeDefersBaseConfigUntilDependenciesAreInjected(): void
    {
        $engine = new \ServiceUserConstructorProbe\engine_double('restored');
        $user = (new ReflectionClass(user::class))->newInstanceWithoutConstructor();

        $user->__unserialize([
            'user_space' => 'main',
            'identifyer' => 'restored',
            'user_data' => $engine,
        ]);

        $this->assertSame($engine, $user->getEngine());
        $this->assertNull($user->getConfig());

        $runtime = new ServiceUserRuntimeDouble();
        $config = $this->baseServiceConfig();
        $configurator = new ServiceUserConfiguratorDouble($config);
        $user->setUserDependencies(
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $runtime,
            $configurator,
            static fn(string $type): object => (object)['type' => $type],
            new user_state(),
            static fn(mixed $identifyer): string => 'restored:' . (string)$identifyer
        );

        $this->assertSame($config, $user->getConfig());
        $this->assertSame([$user], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [user::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame($user, $engine->facadeObject());
        $this->assertInstanceOf(\fan\core\service\config\row::class, $engine->configObject());
    }

    public function testManualSnapshotSerializationUsesInjectedCodec(): void
    {
        $encodedState = null;
        $calls = [];
        $engine = new \ServiceUserConstructorProbe\engine_double('tester');
        $user = (new ReflectionClass(user::class))->newInstanceWithoutConstructor();
        $this->setProperty($user, user::class, 'userSpace', 'main');
        $this->setProperty($user, user::class, 'identifyer', 'tester');
        $this->setProperty($user, user::class, 'userData', $engine);

        $encoder = static function (mixed $state) use (&$encodedState, &$calls): string {
            $encodedState = $state;
            $calls[] = ['encode', array_keys($state)];

            return 'user-snapshot';
        };
        $decoder = static function (string $payload, mixed $default = null) use (&$encodedState, &$calls): mixed {
            $calls[] = ['decode', $payload, $default];

            return $encodedState;
        };

        $user->setUserDependencies(snapshotEncoder: $encoder, snapshotDecoder: $decoder);
        $this->assertSame('user-snapshot', $user->serialize());

        $restored = (new ReflectionClass(user::class))->newInstanceWithoutConstructor();
        $restored->setUserDependencies(snapshotEncoder: $decoder, snapshotDecoder: $decoder);
        $restored->unserialize('user-snapshot');

        $this->assertSame($engine, $restored->getEngine());
        $this->assertSame('main', $restored->getUserSpace());
        $this->assertSame([
            ['encode', ['user_space', 'identifyer', 'user_data']],
            ['decode', 'user-snapshot', []],
        ], $calls);
    }

    public function testMissingSnapshotEncoderFailsAtUseTime(): void
    {
        $user = (new ReflectionClass(user::class))->newInstanceWithoutConstructor();
        $this->setProperty($user, user::class, 'userSpace', 'main');
        $this->setProperty($user, user::class, 'identifyer', 'tester');
        $this->setProperty($user, user::class, 'userData', new \ServiceUserConstructorProbe\engine_double('tester'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Snapshot encoder is not configured for user service.');

        $user->serialize();
    }

    public function testMissingSnapshotDecoderFailsAtUseTime(): void
    {
        $user = (new ReflectionClass(user::class))->newInstanceWithoutConstructor();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Snapshot decoder is not configured for user service.');

        $user->unserialize('user-snapshot');
    }

    public function testNestedStringUserDataSnapshotUsesInjectedDecoder(): void
    {
        $engine = new \ServiceUserConstructorProbe\engine_double('tester');
        $calls = [];
        $decoder = static function (string $payload, mixed $default = null) use ($engine, &$calls): mixed {
            $calls[] = [$payload, $default];

            return $payload === 'user-snapshot'
                ? ['user_space' => 'main', 'identifyer' => 'tester', 'user_data' => 'engine-snapshot']
                : $engine;
        };

        $user = (new ReflectionClass(user::class))->newInstanceWithoutConstructor();
        $user->setUserDependencies(snapshotEncoder: static fn(mixed $state): string => 'unused', snapshotDecoder: $decoder);
        $user->unserialize('user-snapshot');

        $this->assertSame($engine, $user->getEngine());
        $this->assertSame([
            ['user-snapshot', []],
            ['engine-snapshot', null],
        ], $calls);
    }

    private function userService(ServiceUserDataDouble $data, array $applications, ?user_state $state = null): user
    {
        $user = (new ReflectionClass(user::class))->newInstanceWithoutConstructor();

        $this->setProperty($user, user::class, 'userSpace', 'main');
        $this->setProperty($user, user::class, 'identifyer', 'tester');
        $this->setProperty($user, user::class, 'userData', $data);
        $this->setProperty($user, user::class, 'userState', $state ?? new user_state());
        $this->setProperty(
            $user,
            user::class,
            'arrayAdducer',
            \Closure::fromCallable(static fn(mixed $value): array => is_array($value) ? $value : [$value])
        );
        $user->setServiceDependencies(
            serviceListenerState: new \fan\core\service\service_listener_state(),
            classNameResolver: static fn(object $object): string => get_class($object)
        );
        $this->setProperty(
            $user,
            \fan\core\base\service::class,
            'config',
            new ServiceUserConfigDouble([
                'space' => [
                    'main' => \FanTest\core\ConfigRowFactory::row([
                        'APPLICATIONS' => $applications,
                        'ENGINE' => 'config',
                    ]),
                ],
            ])
        );

        return $user;
    }

    private function baseServiceConfig(): ServiceUserConfigDouble
    {
        return new ServiceUserConfigDouble([
            'space' => [
                'main' => \FanTest\core\ConfigRowFactory::row([
                    'APPLICATIONS' => ['crm'],
                    'ENGINE' => 'engine_double',
                ]),
            ],
        ]);
    }

    private function setProperty(object $object, string $class, string $propertyName, mixed $value): void
    {
        $property = new ReflectionProperty($class, $propertyName);
        $property->setValue($object, $value);
    }

}

final class ServiceUserConfigDouble
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

final class ServiceUserDataDouble
{
    public bool $logoutCalled = false;

    public function __construct(private bool $valid, public array $roles = ['member' => null])
    {
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function logout(): void
    {
        $this->logoutCalled = true;
    }

    public function getRoles(bool $force = false): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
}

final class ServiceUserSessionDouble
{
    public ?string $namespace = null;
    public ?string $group = null;

    public function __construct(public array $data)
    {
    }

    public function for(string $namespace, string $group): self
    {
        $this->namespace = $namespace;
        $this->group = $group;

        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }
}

final class ServiceUserApplicationDouble
{
    public function __construct(private string $appName)
    {
    }

    public function getAppName(): string
    {
        return $this->appName;
    }
}

final class ServiceUserConstructorProbe extends user
{
    public function __construct(
        mixed $identifyer,
        string $userSpace,
        ?callable $configFactory,
        ?callable $sessionFactory,
        ?callable $currentUserFactory,
        ?callable $applicationFactory,
        ?callable $errorFactory,
        ?callable $requestInputFactory,
        ?callable $entityFactory,
        ?callable $userEngineFactory,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory,
        ?object $userState = null,
        ?callable $arrayAdducer = null
    )
    {
        parent::__construct(
            $identifyer,
            $userSpace,
            $configFactory,
            $sessionFactory,
            $currentUserFactory,
            $applicationFactory,
            $errorFactory,
            $requestInputFactory,
            $entityFactory,
            $userEngineFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $userState,
            static fn(mixed $identifyer): string => 'key:' . (string)$identifyer,
            null,
            null,
            $arrayAdducer ?? static fn(mixed $value): array => is_array($value) ? $value : [$value]
        );
    }
}

final class ServiceUserRuntimeDouble
{
    public ServiceUserInitializerDouble $initializer;
    public array $loadClassCalls = [];

    public function __construct()
    {
        $this->initializer = new ServiceUserInitializerDouble();
    }

    public function getInitializer(): ServiceUserInitializerDouble
    {
        return $this->initializer;
    }

    public function loadClass(string $class, bool $makeAlias = true): bool
    {
        $this->loadClassCalls[] = [$class, $makeAlias];

        return class_exists($class);
    }

    private ?\fan\core\service\service_listener_state $baseServiceListenerState = null;

    private ?\fan\core\service\service_single_state $baseServiceSingleState = null;

    public function serviceListenerState(): \fan\core\service\service_listener_state
    {
        return $this->baseServiceListenerState ??= new \fan\core\service\service_listener_state();
    }

    public function serviceSingleState(): \fan\core\service\service_single_state
    {
        return $this->baseServiceSingleState ??= new \fan\core\service\service_single_state();
    }

    public function classNameResolver(): callable
    {
        return static fn(object $object): string => get_class($object);
    }
}

final class ServiceUserInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceUserConfiguratorDouble
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
}

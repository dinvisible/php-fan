<?php

declare(strict_types=1);
use fan\core\service\role;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\project\service\error;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../mock/_core/base/DataFunctions.php';
require_once __DIR__ . '/../../../_core/base/expression_evaluator.php';
require_once __DIR__ . '/../../../_core/base/service.php';
require_once __DIR__ . '/../../../_core/base/service/single.php';
require_once __DIR__ . '/../../../_core/service/role.php';

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

class RoleTest extends TestCase
{
    protected function setUp(): void
    {
        error::reset();
    }

    public function testCheckEvaluatesRoleExpressionWithoutEval(): void
    {
        $role = $this->makeRole(['admin', 'editor']);

        $this->assertTrue($role->check('admin'));
        $this->assertTrue($role->check('admin & editor'));
        $this->assertTrue($role->check('admin | missing'));
        $this->assertTrue($role->check('!missing'));
        $this->assertFalse($role->check('admin & missing'));
    }

    public function testCheckReportsInvalidRoleExpression(): void
    {
        $logger = new RoleErrorLoggerDouble();
        $role = $this->makeRole(['admin'], $logger);

        $this->assertFalse($role->check('admin && ('));
        $this->assertSame([['admin && (', 'Incorrect role set']], $logger->messages);
        $this->assertCount(0, error::instance()->messages);
    }

    public function testSessionRolesUseInjectedUserSpaceAndDateFactory(): void
    {
        $dateFactory = new RoleDateFactoryDouble();
        $role = $this->makeRole([], new RoleErrorLoggerDouble(), 'member-space', $dateFactory);

        $this->assertSame($role, $role->setSessionRoles('temporary', 60));

        $this->assertSame(
            ['member-space' => ['temporary' => 'shifted:60']],
            $role->getSessionRoles()
        );
        $this->assertSame([['mysql']], $dateFactory->dates[0]->shiftCalls);
    }

    public function testRoleServiceNoLongerFallsBackToServiceLocator(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../_core/service/role.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('getContainerService(', $source);
        $this->assertStringNotContainsString('containerService(', $source);
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $runtime = new RoleRuntimeDouble();
        $config = new RoleConfigDouble(['CHECK_LOGOUT' => false]);
        $configurator = new RoleConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $session = new RoleSessionDouble([
            role::COMMON_KEY => ['common' => null],
            'constructor-space' => ['member' => null],
        ]);
        $currentUser = new RoleCurrentUserDouble(['admin' => null]);
        $dateFactory = new RoleDateFactoryDouble();

        $role = new RoleConstructorProbe(
            static function (bool $checkLogout) use ($currentUser): RoleCurrentUserDouble {
                $currentUser->checkLogoutValues[] = $checkLogout;

                return $currentUser;
            },
            static fn(string $namespace, string $group): RoleSessionDouble => $session,
            new RoleErrorLoggerDouble(),
            static fn(): string => 'constructor-space',
            $dateFactory,
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            }
        );

        $this->assertSame([RoleConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$role], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [RoleConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame([false], $currentUser->checkLogoutValues);
        $this->assertSame(['common', 'member', 'admin'], $role->getRoles());
        $this->assertSame(['role', 'system'], $session->lastGetByLink[0]);
        $this->assertSame(['role', 'system'], $session->lastGetByLink[1]);
        $this->assertSame([], $cacheFactoryCalls);
    }

    private function makeRole(
        array $roles,
        ?RoleErrorLoggerDouble $logger = null,
        string $userSpace = 'test-space',
        ?RoleDateFactoryDouble $dateFactory = null
    ): role
    {
        $reflection = new \ReflectionClass(role::class);
        $role = $reflection->newInstanceWithoutConstructor();

        $reflection->getProperty('allRoles')->setValue($role, $roles);
        $reflection->getProperty('sessionRoles')->setValue($role, []);
        $reflection->getProperty('fixQttRoles')->setValue($role, []);
        $role->setServiceDependencies(serviceListenerState: new service_listener_state());
        $role->setRoleDependencies(
            null,
            null,
            $logger ?? new RoleErrorLoggerDouble(),
            static fn(): string => $userSpace,
            $dateFactory ?? new RoleDateFactoryDouble()
        );

        return $role;
    }
}

final class RoleConstructorProbe extends role
{
    public function __construct(
        ?callable $currentUserFactory,
        ?callable $sessionFactory,
        ?object $errorLogger,
        ?callable $userSpaceProvider,
        ?callable $dateFactory,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory
    )
    {
        parent::__construct(
            $currentUserFactory,
            $sessionFactory,
            $errorLogger,
            $userSpaceProvider,
            $dateFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory
        );
    }
}

final class RoleRuntimeDouble
{
    public RoleInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new RoleInitializerDouble();
    }

    public function getInitializer(): RoleInitializerDouble
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
}

final class RoleInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class RoleConfiguratorDouble
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

final class RoleConfigDouble
{
    public function __construct(private array $data)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}

final class RoleCurrentUserDouble
{
    public array $checkLogoutValues = [];
    public array $removedRoles = [];

    public function __construct(private array $roles)
    {
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function removeRole(array $roles): void
    {
        $this->removedRoles[] = $roles;
    }
}

final class RoleSessionDouble
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

final class RoleErrorLoggerDouble
{
    public array $messages = [];

    public function logErrorMessage($message, $title = '', $note = '', $fixPosition = false): void
    {
        $this->messages[] = [$message, $title];
    }
}

final class RoleDateFactoryDouble
{
    public array $dates = [];

    public function __invoke(string $date, mixed $format = null): RoleDateDouble
    {
        $date = new RoleDateDouble($date, $format);
        $this->dates[] = $date;

        return $date;
    }
}

final class RoleDateDouble
{
    public array $shiftCalls = [];
    public array $getCalls = [];

    public function __construct(public string $date, public mixed $format)
    {
    }

    public function shiftDate(int|float|string $expiredTime): string
    {
        $this->shiftCalls[] = [$this->format];

        return 'shifted:' . $expiredTime;
    }

    public function get(string $format): string
    {
        $this->getCalls[] = $format;

        return 'formatted:' . $format;
    }
}

<?php

declare(strict_types=1);

use fan\core\di\application_user_service_creator;
use fan\core\di\container;
use fan\core\service\user_state;
use PHPUnit\Framework\TestCase;
use fan\core\service\user as service_user;
use fan\project\service\user;


final class ApplicationUserServiceCreatorTest extends TestCase
{    public function testUserCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = $this->containerWithUserDependencies();
        $state = new user_state();
        $engineFactory = static fn(): object => (object)['engine' => 'user'];
        $identifier = (object)['id' => 42];
        $received = [];

        $user = (new application_user_service_creator())->createUserService(
            $container,
            $state,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'user'];
            },
            $engineFactory,
            $identifier,
            'main'
        );

        $this->assertSame('user', $user->service);
        $this->assertSame('\\' . user::class, $received[0] ?? null);
        $this->assertSame($identifier, $received[1] ?? null);
        $this->assertSame('main', $received[2] ?? null);
        $this->assertSame($container->get('config'), ($received[3])());
        $this->assertSame($container->get('session', service_user::SES_NAMESPACE, 'system'), ($received[4])(service_user::SES_NAMESPACE, 'system'));
        $this->assertSame($container->get('current_user'), ($received[5])());
        $this->assertSame($container->get('application'), ($received[6])());
        $this->assertSame($container->get('error'), ($received[7])());
        $this->assertSame($container->get('request_input'), ($received[8])());
        $this->assertSame($container->get('entity'), ($received[9])());
        $this->assertSame($engineFactory, $received[10] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[11] ?? null);
        $this->assertSame($container->get('config'), $received[12] ?? null);
        $this->assertSame('runtime-cache', ($received[13])('runtime-cache')->type);
        $this->assertSame($state, $received[14] ?? null);
        $this->assertSame('object:42', ($received[15])($identifier));
        $this->assertSame('snapshot', ($received[16])(['state' => true]));
        $this->assertSame(['fallback' => true], ($received[17])('payload', ['fallback' => true]));
        $this->assertSame($container->get('array_adducer'), $received[18] ?? null);
    }

    public function testCurrentUserSpaceAndCurrentUserAreLoadedFromSessionState(): void
    {
        $adminUser = new ApplicationUserStoredUserDouble('admin-id');
        $publicUser = new ApplicationUserStoredUserDouble('public-id');
        $session = new ApplicationUserSessionDouble(
            ['admin' => $adminUser, 'public' => $publicUser],
            ['tools' => 'admin']
        );
        $container = $this->containerWithUserDependencies($session, 'tools');
        $state = new user_state();
        $creator = new application_user_service_creator();

        $this->assertSame('admin', $creator->getCurrentUserSpace($container, $state));
        $this->assertSame($adminUser, $creator->getCurrentUserService($container, $state));
        $this->assertSame('admin', $state->getCurrentUserSpace());
        $this->assertSame($adminUser, $state->getInstance('admin', 'admin-id'));
        $this->assertSame($publicUser, $state->getInstance('public', 'public-id'));
        $this->assertNotEmpty($adminUser->dependencyArguments);
    }

    public function testCurrentUserCheckedLogsOutRequestedUser(): void
    {
        $state = new user_state();
        $user = new ApplicationUserLogoutDouble($state);
        $state->setCurrentUsers(['main' => $user]);
        $request = new ApplicationUserRequestDouble(['logout' => true]);
        $container = $this->containerWithUserDependencies(request: $request);

        $this->assertNull((new application_user_service_creator())->getCurrentUserServiceChecked($container, $state, 'main'));
        $this->assertSame(1, $user->logoutCalls);
        $this->assertSame([['logout', 'GP']], $request->calls);
    }

    public function testCurrentUserCheckedUsesInjectedError500FactoryWhenLogoutLoopDoesNotStop(): void
    {
        $calls = [];
        $state = new user_state();
        $user = new ApplicationUserPersistentLogoutDouble();
        $state->setCurrentUsers(['main' => $user]);
        $container = $this->containerWithUserDependencies(
            request: new ApplicationUserRequestDouble(['logout' => true]),
            error500ExceptionFactory: static function (string $message, int $code, ?Throwable $previous = null) use (&$calls): Throwable {
                $calls[] = [$message, $code, $previous];

                return new RuntimeException('factory: ' . $message, $code, $previous);
            }
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('factory: Too many iteration for logout user.');

        try {
            (new application_user_service_creator())->getCurrentUserServiceChecked($container, $state, 'main');
        } finally {
            $this->assertSame(100, $user->logoutCalls);
            $this->assertSame([
                ['Too many iteration for logout user.', E_USER_ERROR, null],
            ], $calls);
        }
    }

    public function testCurrentUserSpaceUsesInjectedError500FactoryWhenDefaultSpaceIsMissing(): void
    {
        $calls = [];
        $container = $this->containerWithUserDependencies(
            appName: 'unknown',
            config: new ApplicationUserConfigDouble(defaultSpace: '', spaces: []),
            error500ExceptionFactory: static function (string $message, int $code, ?Throwable $previous = null) use (&$calls): Throwable {
                $calls[] = [$message, $code, $previous];

                return new RuntimeException('factory: ' . $message, $code, $previous);
            }
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('factory: Default user space is not set.');

        try {
            (new application_user_service_creator())->getCurrentUserSpace($container, new user_state());
        } finally {
            $this->assertSame([
                ['Default user space is not set.', E_USER_ERROR, null],
            ], $calls);
        }
    }

    public function testUserServiceUsesInjectedError500FactoryWhenRequestedSpaceIsInvalid(): void
    {
        $calls = [];
        $container = $this->containerWithUserDependencies(
            error500ExceptionFactory: static function (string $message, int $code, ?Throwable $previous = null) use (&$calls): Throwable {
                $calls[] = [$message, $code, $previous];

                return new RuntimeException('factory: ' . $message, $code, $previous);
            }
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('factory: Incorrect identifyer of user space - "missing".');

        try {
            (new application_user_service_creator())->createUserService(
                $container,
                new user_state(),
                static fn(): object => new stdClass(),
                static fn(): object => new stdClass(),
                7,
                'missing'
            );
        } finally {
            $this->assertSame([
                ['Incorrect identifyer of user space - "missing".', E_USER_ERROR, null],
            ], $calls);
        }
    }

    private function containerWithUserDependencies(
        ?ApplicationUserSessionDouble $session = null,
        string $appName = 'crm',
        ?ApplicationUserRequestDouble $request = null,
        ?ApplicationUserConfigDouble $config = null,
        ?callable $error500ExceptionFactory = null
    ): container {
        $session ??= new ApplicationUserSessionDouble();
        $request ??= new ApplicationUserRequestDouble();
        $config ??= new ApplicationUserConfigDouble();

        $container = new container();
        $container
            ->set('serializer_operations', new ApplicationUserSerializerOperationsDouble())
            ->set('config', $config)
            ->factory('session', static fn(container $container, string $namespace, string $group): ApplicationUserSessionDouble => $session, false)
            ->set('current_user', (object)['name' => 'current-user'])
            ->set('application', new ApplicationUserApplicationDouble($appName))
            ->set('error', (object)['name' => 'error'])
            ->set('request_input', (object)['name' => 'request-input'])
            ->set('entity', (object)['name' => 'entity'])
            ->set('bootstrap_runtime', (object)['name' => 'runtime'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('array_adducer', static fn(): callable => static fn(mixed $value): array => is_array($value) ? $value : [$value])
            ->set('request', $request)
            ->set(
                'error500_exception_factory',
                $error500ExceptionFactory
                    ?? static fn(string $message, int $code = E_USER_ERROR, ?Throwable $previous = null): Throwable => new RuntimeException($message, $code, $previous)
            );

        return $container;
    }
}

final class ApplicationUserConfigDouble
{
    /**
     * @var array<string, object>
     */
    private array $spaces;

    public function __construct(private string $defaultSpace = 'main', ?array $spaces = null)
    {
        $this->spaces = $spaces ?? [
            'main' => (object)['APPLICATIONS' => ['crm'], 'ENGINE' => 'config'],
            'admin' => (object)['APPLICATIONS' => ['tools'], 'ENGINE' => 'config'],
            'public' => (object)['APPLICATIONS' => ['crm', 'tools'], 'ENGINE' => 'config'],
        ];
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === 'user' && is_array($default) && ($default[0] ?? null) === 'space') {
            return $this->spaces[(string)($default[1] ?? '')] ?? null;
        }

        return $key === 'user'
            ? new ApplicationUserConfigRowDouble($this->spaces, $this->defaultSpace)
            : $default;
    }
}

final class ApplicationUserConfigRowDouble
{
    /**
     * @param array<string, object> $spaces
     */
    public function __construct(private array $spaces, private string $defaultSpace)
    {
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return match ($key) {
            'space' => $this->spaces,
            'DEFAULT_SPACE' => $this->defaultSpace,
            default => $default,
        };
    }
}

final class ApplicationUserSerializerOperationsDouble
{
    public function stableKeyEncoder(): callable
    {
        return static fn(mixed $value): string => is_object($value) && isset($value->id) ? 'object:' . $value->id : 'encoded';
    }

    public function phpSnapshotEncoder(): callable
    {
        return static fn(mixed $value): string => 'snapshot';
    }

    public function phpSnapshotDecoder(): callable
    {
        return static fn(string $payload, mixed $default = null): mixed => $default;
    }
}

final class ApplicationUserSessionDouble
{
    /**
     * @param array<string, object> $currents
     * @param array<string, string> $priority
     */
    public function __construct(private array $currents = [], private array $priority = [])
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return match ($key) {
            'currents' => $this->currents,
            'priority' => $this->priority,
            default => $default,
        };
    }
}

final class ApplicationUserStoredUserDouble
{
    public array $dependencyArguments = [];

    public function __construct(public mixed $identifyer)
    {
    }

    public function setUserDependencies(mixed ...$arguments): void
    {
        $this->dependencyArguments = $arguments;
    }
}

final class ApplicationUserLogoutDouble
{
    public int $logoutCalls = 0;

    public function __construct(private user_state $state)
    {
    }

    public function getConfig(string $key, mixed $default = null): mixed
    {
        return match ($key) {
            'LOGOUT_FIELD' => 'logout',
            'LOGOUT_ORDER' => 'GP',
            default => $default,
        };
    }

    public function logout(): void
    {
        $this->logoutCalls++;
        $this->state->setCurrentUsers([]);
    }
}

final class ApplicationUserPersistentLogoutDouble
{
    public int $logoutCalls = 0;

    public function getConfig(string $key, mixed $default = null): mixed
    {
        return match ($key) {
            'LOGOUT_FIELD' => 'logout',
            'LOGOUT_ORDER' => 'GP',
            default => $default,
        };
    }

    public function logout(): void
    {
        $this->logoutCalls++;
    }
}

final class ApplicationUserRequestDouble
{
    public array $calls = [];

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(private array $values = [])
    {
    }

    public function get(string $field, string $order): mixed
    {
        $this->calls[] = [$field, $order];

        return $this->values[$field] ?? null;
    }
}

final class ApplicationUserApplicationDouble
{
    public function __construct(private string $appName)
    {
    }

    public function getAppName(): string
    {
        return $this->appName;
    }
}

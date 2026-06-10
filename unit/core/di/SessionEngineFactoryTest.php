<?php

declare(strict_types=1);

use fan\core\di\session_engine_factory;
use PHPUnit\Framework\TestCase;
use fan\core\service\session\inbuilt;
use fan\core\service\session\pear;

final class SessionEngineFactoryTest extends TestCase
{
    public function testFactoryCreatesPearSessionEngineWithConfigArrayAndLoader(): void
    {
        $databaseConfig = new stdClass();
        $requestService = new stdClass();
        $loader = new stdClass();
        $delegatedArguments = null;
        $factory = new session_engine_factory(
            static function (string $className, array $arguments) use (&$delegatedArguments): object {
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            },
            new stdClass(),
            null
        );

        $engine = $factory(
            SessionEngineFactoryPearEngineDouble::class,
            'sid-456',
            new SessionEngineFactoryConfigDouble(['SESSION_NAME' => 'SID']),
            $databaseConfig,
            new stdClass(),
            static fn(): object => new stdClass(),
            $requestService,
            $loader
        );

        $this->assertSame([['SESSION_NAME' => 'SID'], $databaseConfig, $requestService, $loader], $delegatedArguments);
        $this->assertInstanceOf(SessionEngineFactoryPearEngineDouble::class, $engine);
        $this->assertSame([['SESSION_NAME' => 'SID'], $databaseConfig, $requestService, $loader], $engine->constructorArgs);
    }

    public function testFactoryCreatesDefaultSessionEngineWithSidInputAndErrorFactory(): void
    {
        $requestInput = new stdClass();
        $errorFactory = static fn(): object => new stdClass();
        $delegatedArguments = null;
        $factory = new session_engine_factory(
            static function (string $className, array $arguments) use (&$delegatedArguments): object {
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            },
            new stdClass(),
            null
        );

        $engine = $factory(
            SessionEngineFactoryDefaultEngineDouble::class,
            'sid-789',
            new SessionEngineFactoryConfigDouble([]),
            null,
            $requestInput,
            $errorFactory,
            null,
            null
        );

        $this->assertSame(['sid-789', $requestInput, $errorFactory], $delegatedArguments);
        $this->assertInstanceOf(SessionEngineFactoryDefaultEngineDouble::class, $engine);
        $this->assertSame(['sid-789', $requestInput, $errorFactory], $engine->constructorArgs);
    }

    public function testFactoryCreatesCoreInbuiltSessionEngineWithNativeSessionAdapter(): void
    {
        $nativeSession = new SessionEngineFactoryNativeSessionDouble();
        $factory = new session_engine_factory(
            static function (): object {
                throw new RuntimeException('Configured session engine factory should not be called for core inbuilt engine.');
            },
            $nativeSession,
            null
        );

        $engine = $factory(
            inbuilt::class,
            'sid-core',
            new SessionEngineFactoryConfigDouble([]),
            null,
            new SessionEngineFactoryRequestInputDouble(),
            static fn(): object => new stdClass(),
            null,
            null
        );

        $this->assertInstanceOf(inbuilt::class, $engine);
        $this->assertSame([['id', 'sid-core'], ['start']], $nativeSession->calls);
    }

    public function testFactoryCreatesCorePearSessionEngineWithHttpSessionAdapter(): void
    {
        $httpSession = new SessionEngineFactoryPearHttpSessionDouble();
        $loader = new SessionEngineFactoryPearLoaderDouble();
        $request = new SessionEngineFactoryPearRequestDouble(['SID' => 'sid-pear']);
        $factory = new session_engine_factory(
            static function (): object {
                throw new RuntimeException('Configured session engine factory should not be called for core PEAR engine.');
            },
            new stdClass(),
            $httpSession
        );

        $engine = $factory(
            pear::class,
            'sid-core',
            new SessionEngineFactoryConfigDouble(['SESSION_NAME' => 'SID']),
            null,
            new stdClass(),
            static fn(): object => new stdClass(),
            $request,
            $loader
        );

        $this->assertInstanceOf(pear::class, $engine);
        $this->assertSame(1, $loader->loadCalls);
        $this->assertSame([true], $httpSession->useCookiesCalls);
        $this->assertSame([['SID', 'sid-pear']], $httpSession->startCalls);
    }

    public function testFactoryRequiresHttpSessionAdapterForCorePearSessionEngine(): void
    {
        $factory = new session_engine_factory(
            static function (): object {
                throw new RuntimeException('Configured session engine factory should not be called for core PEAR engine.');
            },
            new stdClass(),
            null
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('PEAR HTTP session adapter is not configured for session engine factory.');

        $factory(
            pear::class,
            'sid-core',
            new SessionEngineFactoryConfigDouble(['SESSION_NAME' => 'SID']),
            null,
            new stdClass(),
            static fn(): object => new stdClass(),
            new SessionEngineFactoryPearRequestDouble(['SID' => 'sid-pear']),
            new SessionEngineFactoryPearLoaderDouble()
        );
    }

            }


final class SessionEngineFactoryConfigDouble
{
    public function __construct(private array $config)
    {
    }

    public function toArray(): array
    {
        return $this->config;
    }
}

final class SessionEngineFactoryPearEngineDouble extends pear
{
    public array $constructorArgs = [];

    public function __construct(
        mixed $config,
        ?object $databaseConfig = null,
        ?object $request = null,
        ?object $sessionSupportLoader = null
    ) {
        $this->constructorArgs = func_get_args();
    }
}

final class SessionEngineFactoryDefaultEngineDouble
{
    public array $constructorArgs = [];

    public function __construct(?string $sid, ?object $input = null, ?callable $errorFactory = null)
    {
        $this->constructorArgs = func_get_args();
    }
}

final class SessionEngineFactoryRequestInputDouble
{
    public array $session = [];

    public function &sessionValue(string $group, string $name): mixed
    {
        if (!isset($this->session[$group])) {
            $this->session[$group] = [];
        }
        if (!array_key_exists($name, $this->session[$group])) {
            $this->session[$group][$name] = null;
        }

        return $this->session[$group][$name];
    }

    public function &sessionRoot(): array
    {
        return $this->session;
    }
}

final class SessionEngineFactoryNativeSessionDouble
{
    public array $calls = [];

    public function start(): bool
    {
        $this->calls[] = ['start'];

        return true;
    }

    public function id(?string $id = null): string|false
    {
        if ($id !== null) {
            $this->calls[] = ['id', $id];
        }

        return $id ?? 'sid-core';
    }

    public function name(): string|false
    {
        return 'PHPSESSID';
    }

    public function status(): int
    {
        return PHP_SESSION_NONE;
    }

    public function destroy(): bool
    {
        return true;
    }
}

final class SessionEngineFactoryPearLoaderDouble
{
    public int $loadCalls = 0;

    public function load(): void
    {
        $this->loadCalls++;
    }
}

final class SessionEngineFactoryPearRequestDouble
{
    public function __construct(private array $data)
    {
    }

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }
}

final class SessionEngineFactoryPearHttpSessionDouble
{
    public array $useCookiesCalls = [];

    public array $startCalls = [];

    public function setContainer(string $type, array $param): void
    {
    }

    public function useCookies(bool $useCookies): void
    {
        $this->useCookiesCalls[] = $useCookies;
    }

    public function start(string $sessionName, mixed $sid = null): void
    {
        $this->startCalls[] = [$sessionName, $sid];
    }

    public function id(): string
    {
        return 'pear-session-id';
    }

    public function get(string $key, ?string $defaultValue = null): mixed
    {
        return $defaultValue;
    }

    public function set(string $key, mixed $value): void
    {
    }

    public function clear(): void
    {
    }

    public function destroy(): void
    {
    }
}

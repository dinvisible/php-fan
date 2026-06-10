<?php

declare(strict_types=1);

use fan\core\di\error_service_factory;
use fan\core\service\error;
use PHPUnit\Framework\TestCase;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;

final class ErrorServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreErrorServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $input = new ErrorServiceFactoryInputDouble(['SERVER_NAME' => 'example.test']);
        $runtime = new ErrorServiceFactoryRuntimeDouble();
        $config = new ErrorServiceFactoryConfigDouble();
        $configurator = new ErrorServiceFactoryConfiguratorDouble($config);
        $logFactory = static fn(): object => new stdClass();
        $emailFactory = static fn(): object => new stdClass();
        $cacheFactoryCalls = [];
        $cacheFactory = static function (string $type) use (&$cacheFactoryCalls): object {
            $cacheFactoryCalls[] = $type;

            return (object)['type' => $type];
        };
        $phpArrayFileLoader = static fn(string $path, mixed $default = null): mixed => $default;
        $errorLogWriter = new ErrorServiceFactoryLogWriterDouble();
        $fileStorage = new ErrorServiceFactoryFileStorageDouble();
        $overrideCalls = [];

        $error = (new error_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            error::class,
            true,
            $input,
            $runtime,
            $logFactory,
            $emailFactory,
            $runtime,
            $configurator,
            $cacheFactory,
            $phpArrayFileLoader,
            $errorLogWriter,
            $fileStorage
        );

        $this->assertInstanceOf(error::class, $error);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([error::class], $runtime->initializer->serviceParams);
        $this->assertSame([$error], $configurator->getServiceConfigCalls);
        $this->assertContains($configurator->resetCalls[0][0] ?? null, ['error', error::class]);
        $this->assertSame('ENABLED', $configurator->resetCalls[0][1] ?? null);
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $input = new stdClass();
        $runtime = new stdClass();
        $logFactory = static fn(): object => new stdClass();
        $emailFactory = static fn(): object => new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $phpArrayFileLoader = static fn(string $path, mixed $default = null): mixed => $default;
        $errorLogWriter = new stdClass();
        $fileStorage = new stdClass();
        $configuredCalls = [];

        $error = (new error_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            ErrorServiceFactoryProbe::class,
            true,
            $input,
            $runtime,
            $logFactory,
            $emailFactory,
            $runtime,
            $configurator,
            $cacheFactory,
            $phpArrayFileLoader,
            $errorLogWriter,
            $fileStorage
        );

        $this->assertInstanceOf(ErrorServiceFactoryProbe::class, $error);
        $this->assertTrue($error->allowIni);
        $this->assertSame($input, $error->input);
        $this->assertSame($runtime, $error->runtime);
        $this->assertSame($logFactory, $error->logFactory);
        $this->assertSame($emailFactory, $error->emailFactory);
        $this->assertSame($runtime, $error->serviceBootstrapRuntime);
        $this->assertSame($configurator, $error->serviceConfigurator);
        $this->assertSame($cacheFactory, $error->serviceCacheFactory);
        $this->assertSame($phpArrayFileLoader, $error->phpArrayFileLoader);
        $this->assertSame($errorLogWriter, $error->errorLogWriter);
        $this->assertSame($fileStorage, $error->fileStorage);
        $this->assertSame(ErrorServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([
            true,
            $input,
            $runtime,
            $logFactory,
            $emailFactory,
            $runtime,
            $configurator,
            $cacheFactory,
            $phpArrayFileLoader,
            $errorLogWriter,
            $fileStorage,
        ], $configuredCalls[0][1] ?? null);
    }

    public function testFactoryAllowsRemovedEmailServiceDependency(): void
    {
        $input = new stdClass();
        $runtime = new stdClass();
        $logFactory = static fn(): object => new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $phpArrayFileLoader = static fn(string $path, mixed $default = null): mixed => $default;

        $error = (new error_service_factory(
            static fn(string $className, array $arguments): object => new $className(...$arguments)
        ))(
            ErrorServiceFactoryProbe::class,
            true,
            $input,
            $runtime,
            $logFactory,
            null,
            $runtime,
            $configurator,
            $cacheFactory,
            $phpArrayFileLoader,
            new stdClass(),
            new stdClass()
        );

        $this->assertNull($error->emailFactory);
    }

    public function testFactoryReturnsAlreadyRegisteredErrorSingleton(): void
    {
        $runtime = new ErrorServiceFactoryRuntimeDouble();
        $existing = new stdClass();
        $runtime->serviceSingleState()->setInstance('fan\project\service\error', $existing);
        $configuredCalls = [];

        $error = (new error_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            error::class,
            true,
            new ErrorServiceFactoryInputDouble(['SERVER_NAME' => 'example.test']),
            $runtime,
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            $runtime,
            new ErrorServiceFactoryConfiguratorDouble(new ErrorServiceFactoryConfigDouble()),
            static fn(string $type): object => (object)['type' => $type],
            static fn(string $path, mixed $default = null): mixed => $default,
            new ErrorServiceFactoryLogWriterDouble(),
            new ErrorServiceFactoryFileStorageDouble()
        );

        $this->assertSame($existing, $error);
        $this->assertSame([], $configuredCalls);
        $this->assertSame([], $runtime->initializer->serviceParams);
    }

    private function ensureBaseFunctionAliases(): void
    {
        if (!function_exists('get_class_name')) {
            eval('function get_class_name(string|object $object): ?string { if (is_object($object)) { $object = get_class($object); } $parts = explode("\\\\\\\\", $object); return end($parts); }');
        }
        if (!function_exists('fan\core\base\get_class_name')) {
            eval('namespace fan\core\base { function get_class_name(string|object $object): ?string { return \get_class_name($object); } }');
        }
    }
}

final class ErrorServiceFactoryProbe
{
    public function __construct(
        public bool $allowIni,
        public object $input,
        public object $runtime,
        public $logFactory,
        public $emailFactory,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory,
        public $phpArrayFileLoader,
        public object $errorLogWriter,
        public object $fileStorage
    ) {
    }
}

final class ErrorServiceFactoryFileStorageDouble
{
    public function isDirectory(string $path): bool
    {
        return true;
    }
}

final class ErrorServiceFactoryInputDouble
{
    public function __construct(private array $server)
    {
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }
}

final class ErrorServiceFactoryRuntimeDouble
{
    public ErrorServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ErrorServiceFactoryInitializerDouble();
    }

    public function getInitializer(): ErrorServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    public function isCli(): bool
    {
        return false;
    }

    public function parsePath(string $path): string
    {
        return $path;
    }

    public function classNameResolver(): callable
    {
        return static fn(object|string $object): ?string => get_class_name($object);
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

final class ErrorServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ErrorServiceFactoryConfiguratorDouble
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

final class ErrorServiceFactoryConfigDouble implements ArrayAccess
{
    private array $values = [
        'DUPLICATE_BY_EMAIL' => [],
        'SYS_MASK' => E_ALL,
        'SYS_ERR' => [],
        'IGNORE_PATH' => [],
    ];

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->values;
        }

        return $this->values[$key] ?? $default;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->values);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->values[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->values[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->values[$offset]);
    }
}

final class ErrorServiceFactoryLogWriterDouble
{
    public array $writes = [];

    public function write(string $message, int $messageType = 0, ?string $destination = null): bool
    {
        $this->writes[] = [$message, $messageType, $destination];

        return true;
    }
}

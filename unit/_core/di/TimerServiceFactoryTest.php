<?php

declare(strict_types=1);

use fan\core\di\timer_service_factory;
use fan\core\service\timer;
use PHPUnit\Framework\TestCase;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;

final class TimerServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreTimerServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $runtime = new TimerServiceFactoryRuntimeDouble();
        $dateFactory = static fn(): object => new stdClass();
        $entityFactory = static fn(): object => new stdClass();
        $errorFactory = static fn(): object => new stdClass();
        $logFactory = static fn(): object => new stdClass();
        $emailFactory = static fn(string $name): object => (object)['name' => $name];
        $programFactory = static fn(string $className): object => new $className();
        $configurator = new TimerServiceFactoryConfiguratorDouble(new TimerServiceFactoryConfigDouble());
        $cacheFactoryCalls = [];
        $cacheFactory = static function (string $type) use (&$cacheFactoryCalls): object {
            $cacheFactoryCalls[] = $type;

            return (object)['type' => $type];
        };
        $overrideCalls = [];

        $service = (new timer_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            timer::class,
            $runtime,
            $dateFactory,
            $entityFactory,
            $errorFactory,
            $logFactory,
            $emailFactory,
            $programFactory,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(timer::class, $service);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([timer::class], $runtime->initializer->serviceParams);
        $this->assertSame([
            '{PROJECT}/timer/',
        ], $runtime->parsedPaths);
        $this->assertSame([$service], $configurator->getServiceConfigCalls);
        $this->assertContains($configurator->resetCalls[0][0] ?? null, ['timer', timer::class]);
        $this->assertSame('ENABLED', $configurator->resetCalls[0][1] ?? null);
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $runtime = new stdClass();
        $dateFactory = static fn(): object => new stdClass();
        $entityFactory = static fn(): object => new stdClass();
        $errorFactory = static fn(): object => new stdClass();
        $logFactory = static fn(): object => new stdClass();
        $emailFactory = static fn(string $name): object => (object)['name' => $name];
        $programFactory = static fn(string $className): object => new $className();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $configuredCalls = [];

        $timer = (new timer_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            TimerServiceFactoryProbe::class,
            $runtime,
            $dateFactory,
            $entityFactory,
            $errorFactory,
            $logFactory,
            $emailFactory,
            $programFactory,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(TimerServiceFactoryProbe::class, $timer);
        $this->assertSame($runtime, $timer->runtime);
        $this->assertSame($dateFactory, $timer->dateFactory);
        $this->assertSame($entityFactory, $timer->entityFactory);
        $this->assertSame($errorFactory, $timer->errorFactory);
        $this->assertSame($logFactory, $timer->logFactory);
        $this->assertSame($emailFactory, $timer->emailFactory);
        $this->assertSame($programFactory, $timer->programFactory);
        $this->assertSame($runtime, $timer->serviceBootstrapRuntime);
        $this->assertSame($configurator, $timer->serviceConfigurator);
        $this->assertSame($cacheFactory, $timer->serviceCacheFactory);
        $this->assertSame(TimerServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([
            $runtime,
            $dateFactory,
            $entityFactory,
            $errorFactory,
            $logFactory,
            $emailFactory,
            $programFactory,
            $runtime,
            $configurator,
            $cacheFactory,
        ], $configuredCalls[0][1] ?? null);
    }

    public function testFactoryAllowsRemovedEmailServiceDependency(): void
    {
        $runtime = new stdClass();
        $dateFactory = static fn(): object => new stdClass();
        $entityFactory = static fn(): object => new stdClass();
        $errorFactory = static fn(): object => new stdClass();
        $logFactory = static fn(): object => new stdClass();
        $programFactory = static fn(string $className): object => new $className();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];

        $timer = (new timer_service_factory(
            static fn(string $className, array $arguments): object => new $className(...$arguments)
        ))(
            TimerServiceFactoryProbe::class,
            $runtime,
            $dateFactory,
            $entityFactory,
            $errorFactory,
            $logFactory,
            null,
            $programFactory,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertNull($timer->emailFactory);
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

final class TimerServiceFactoryProbe
{
    public function __construct(
        public object $runtime,
        public $dateFactory,
        public $entityFactory,
        public $errorFactory,
        public $logFactory,
        public $emailFactory,
        public $programFactory,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory
    ) {
    }
}

final class TimerServiceFactoryRuntimeDouble
{
    public TimerServiceFactoryInitializerDouble $initializer;
    public array $parsedPaths = [];

    public function __construct()
    {
        $this->initializer = new TimerServiceFactoryInitializerDouble();
    }

    public function getInitializer(): TimerServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    public function parsePath(string $path): string
    {
        $this->parsedPaths[] = $path;

        return $path;
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
        return static fn(object|string $object): ?string => get_class_name($object);
    }
}

final class TimerServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class TimerServiceFactoryConfiguratorDouble
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

final class TimerServiceFactoryConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}

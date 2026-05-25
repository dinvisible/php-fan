<?php

declare(strict_types=1);

use fan\core\di\reflector_service_factory;
use fan\core\service\reflector;
use PHPUnit\Framework\TestCase;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;

final class ReflectorServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreReflectorServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $runtime = new ReflectorServiceFactoryRuntimeDouble();
        $config = new ReflectorServiceFactoryConfigDouble();
        $configurator = new ReflectorServiceFactoryConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $cacheFactory = static function (string $type) use (&$cacheFactoryCalls): object {
            $cacheFactoryCalls[] = $type;

            return (object)['type' => $type];
        };
        $overrideCalls = [];
        $reflectionClassFactory = new stdClass();

        $reflector = (new reflector_service_factory(static function (string $className, array $arguments) use (&$overrideCalls): object {
            $overrideCalls[] = [$className, $arguments];

            return new stdClass();
        }))(
            reflector::class,
            $runtime,
            $configurator,
            $cacheFactory,
            $reflectionClassFactory
        );

        $this->assertInstanceOf(reflector::class, $reflector);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([reflector::class], $runtime->initializer->serviceParams);
        $this->assertSame([$reflector], $configurator->getServiceConfigCalls);
        $this->assertContains($configurator->resetCalls[0][0] ?? null, ['reflector', reflector::class]);
        $this->assertSame('ENABLED', $configurator->resetCalls[0][1] ?? null);
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $configuredCalls = [];
        $reflectionClassFactory = new stdClass();

        $reflector = (new reflector_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(ReflectorServiceFactoryProbe::class, $runtime, $configurator, $cacheFactory, $reflectionClassFactory);

        $this->assertInstanceOf(ReflectorServiceFactoryProbe::class, $reflector);
        $this->assertSame($runtime, $reflector->serviceBootstrapRuntime);
        $this->assertSame($configurator, $reflector->serviceConfigurator);
        $this->assertSame($cacheFactory, $reflector->serviceCacheFactory);
        $this->assertSame($reflectionClassFactory, $reflector->reflectionClassFactory);
        $this->assertSame(ReflectorServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([$runtime, $configurator, $cacheFactory, $reflectionClassFactory], $configuredCalls[0][1] ?? null);
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

final class ReflectorServiceFactoryProbe
{
    public function __construct(
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory,
        public $reflectionClassFactory
    ) {
    }
}


final class ReflectorServiceFactoryRuntimeDouble
{
    public ReflectorServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ReflectorServiceFactoryInitializerDouble();
    }

    public function getInitializer(): ReflectorServiceFactoryInitializerDouble
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

final class ReflectorServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ReflectorServiceFactoryConfiguratorDouble
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

final class ReflectorServiceFactoryConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}

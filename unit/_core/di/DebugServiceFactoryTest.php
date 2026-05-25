<?php

declare(strict_types=1);

use fan\core\di\debug_service_factory;
use fan\core\service\debug;
use PHPUnit\Framework\TestCase;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;

final class DebugServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreDebugServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $tab = new DebugServiceFactoryTabDouble();
        $input = new DebugServiceFactoryInputDouble(['SERVER_ADDR' => '127.0.0.1']);
        $runtime = new DebugServiceFactoryRuntimeDouble();
        $config = new DebugServiceFactoryConfigDouble([
            'ENABLED' => true,
            'DEBUG_IP' => '/^127\.0\.0\.1$/',
        ]);
        $configurator = new DebugServiceFactoryConfiguratorDouble($config);
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $metaFileStorage = new stdClass();
        $arrayAdducer = static fn(mixed $value): array => is_array($value) ? $value : [$value];
        $overrideCalls = [];
        $reflectionClassFactory = new stdClass();

        $debug = (new debug_service_factory(static function (string $className, array $arguments) use (&$overrideCalls): object {
            $overrideCalls[] = [$className, $arguments];

            return new stdClass();
        }))(
            debug::class,
            true,
            $tab,
            $input,
            $runtime,
            $configurator,
            $cacheFactory,
            $metaFileStorage,
            $arrayAdducer,
            $reflectionClassFactory
        );

        $this->assertInstanceOf(debug::class, $debug);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([debug::class], $runtime->initializer->serviceParams);
        $this->assertSame([$debug], $configurator->getServiceConfigCalls);
        $this->assertContains($configurator->resetCalls[0][0] ?? null, ['debug', debug::class]);
        $this->assertSame('ENABLED', $configurator->resetCalls[0][1] ?? null);
        $this->assertTrue($debug->isEnabled());
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $tab = new stdClass();
        $input = new stdClass();
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $metaFileStorage = new stdClass();
        $arrayAdducer = static fn(mixed $value): array => is_array($value) ? $value : [$value];
        $configuredCalls = [];
        $reflectionClassFactory = new stdClass();

        $debug = (new debug_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(DebugServiceFactoryProbe::class, true, $tab, $input, $runtime, $configurator, $cacheFactory, $metaFileStorage, $arrayAdducer, $reflectionClassFactory);

        $this->assertInstanceOf(DebugServiceFactoryProbe::class, $debug);
        $this->assertTrue($debug->allowIni);
        $this->assertSame($tab, $debug->tab);
        $this->assertSame($input, $debug->input);
        $this->assertSame($runtime, $debug->serviceBootstrapRuntime);
        $this->assertSame($configurator, $debug->serviceConfigurator);
        $this->assertSame($cacheFactory, $debug->serviceCacheFactory);
        $this->assertSame($metaFileStorage, $debug->metaFileStorage);
        $this->assertSame($arrayAdducer, $debug->arrayAdducer);
        $this->assertSame($reflectionClassFactory, $debug->reflectionClassFactory);
        $this->assertSame(DebugServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([true, $tab, $input, $runtime, $configurator, $cacheFactory, $metaFileStorage, $arrayAdducer, $reflectionClassFactory], $configuredCalls[0][1] ?? null);
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

final class DebugServiceFactoryProbe
{
    public function __construct(
        public bool $allowIni,
        public object $tab,
        public object $input,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory,
        public object $metaFileStorage,
        public $arrayAdducer,
        public $reflectionClassFactory
    ) {
    }
}


final class DebugServiceFactoryTabDouble
{
}

final class DebugServiceFactoryInputDouble
{
    public function __construct(private array $server = [])
    {
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }
}

final class DebugServiceFactoryRuntimeDouble
{
    public DebugServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new DebugServiceFactoryInitializerDouble();
    }

    public function getInitializer(): DebugServiceFactoryInitializerDouble
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
        return static fn(object $object): string => get_class($object);
    }
}

final class DebugServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class DebugServiceFactoryConfigDouble extends ArrayObject
{
    public function __construct(private array $data)
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

final class DebugServiceFactoryConfiguratorDouble
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

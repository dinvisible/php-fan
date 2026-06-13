<?php

declare(strict_types=1);

use fan\core\di\plain_service_factory;
use fan\core\service\plain;
use PHPUnit\Framework\TestCase;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;

final class PlainServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCorePlainServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $matcher = new stdClass();
        $plainConfigFactory = static fn(): object => new stdClass();
        $header = new stdClass();
        $controllerDependenciesFactory = static fn(): array => [];
        $controllerFactory = static fn(): object => new stdClass();
        $runtime = new PlainServiceFactoryRuntimeDouble();
        $config = new PlainServiceFactoryConfigDouble();
        $configurator = new PlainServiceFactoryConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $cacheFactory = static function (string $type) use (&$cacheFactoryCalls): object {
            $cacheFactoryCalls[] = $type;

            return (object)['type' => $type];
        };
        $overrideCalls = [];

        $plain = (new plain_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            plain::class,
            true,
            $matcher,
            $plainConfigFactory,
            $header,
            $controllerDependenciesFactory,
            $controllerFactory,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(plain::class, $plain);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([plain::class], $runtime->initializer->serviceParams);
        $this->assertSame([$plain], $configurator->getServiceConfigCalls);
        $this->assertContains($configurator->resetCalls[0][0] ?? null, ['plain', plain::class]);
        $this->assertSame('ENABLED', $configurator->resetCalls[0][1] ?? null);
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $matcher = new stdClass();
        $plainConfigFactory = static fn(): object => new stdClass();
        $header = new stdClass();
        $controllerDependenciesFactory = static fn(): array => [];
        $controllerFactory = static fn(): object => new stdClass();
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $configuredCalls = [];

        $plain = (new plain_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            PlainServiceFactoryProbe::class,
            true,
            $matcher,
            $plainConfigFactory,
            $header,
            $controllerDependenciesFactory,
            $controllerFactory,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(PlainServiceFactoryProbe::class, $plain);
        $this->assertTrue($plain->allowIni);
        $this->assertSame($matcher, $plain->matcher);
        $this->assertSame($plainConfigFactory, $plain->plainConfigFactory);
        $this->assertSame($header, $plain->header);
        $this->assertSame($controllerDependenciesFactory, $plain->controllerDependenciesFactory);
        $this->assertSame($controllerFactory, $plain->controllerFactory);
        $this->assertSame($runtime, $plain->serviceBootstrapRuntime);
        $this->assertSame($configurator, $plain->serviceConfigurator);
        $this->assertSame($cacheFactory, $plain->serviceCacheFactory);
        $this->assertSame(PlainServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([
            true,
            $matcher,
            $plainConfigFactory,
            $header,
            $controllerDependenciesFactory,
            $controllerFactory,
            $runtime,
            $configurator,
            $cacheFactory,
            null,
        ], $configuredCalls[0][1] ?? null);
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

final class PlainServiceFactoryProbe
{
    public function __construct(
        public bool $allowIni,
        public object $matcher,
        public $plainConfigFactory,
        public object $header,
        public $controllerDependenciesFactory,
        public $controllerFactory,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory,
        public $controllerClassExists = null
    ) {
    }
}

final class PlainServiceFactoryRuntimeDouble
{
    public PlainServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new PlainServiceFactoryInitializerDouble();
    }

    public function getInitializer(): PlainServiceFactoryInitializerDouble
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
        return static fn(object|string $object): ?string => get_class_name($object);
    }
}

final class PlainServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class PlainServiceFactoryConfiguratorDouble
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

final class PlainServiceFactoryConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}


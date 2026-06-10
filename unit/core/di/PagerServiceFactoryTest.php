<?php

declare(strict_types=1);

use fan\core\di\pager_service_factory;
use fan\core\service\pager;
use PHPUnit\Framework\TestCase;
use fan\core\block\base;

final class PagerServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCorePagerServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $block = new PagerServiceFactoryBlockDouble();
        $entityFactory = static fn(): object => new stdClass();
        $tabFactory = static fn(): object => new stdClass();
        $requestFactory = static fn(): object => new stdClass();
        $runtime = new PagerServiceFactoryRuntimeDouble();
        $config = new PagerServiceFactoryConfigDouble();
        $configurator = new PagerServiceFactoryConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $cacheFactory = static function (string $type) use (&$cacheFactoryCalls): object {
            $cacheFactoryCalls[] = $type;

            return (object)['type' => $type];
        };
        $overrideCalls = [];

        $pager = (new pager_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            pager::class,
            $block,
            $entityFactory,
            $tabFactory,
            $requestFactory,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(pager::class, $pager);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([pager::class], $runtime->initializer->serviceParams);
        $this->assertSame([$pager], $configurator->getServiceConfigCalls);
        $this->assertContains($configurator->resetCalls[0][0] ?? null, ['pager', pager::class]);
        $this->assertSame('ENABLED', $configurator->resetCalls[0][1] ?? null);
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $block = new PagerServiceFactoryBlockDouble();
        $entityFactory = static fn(): object => new stdClass();
        $tabFactory = static fn(): object => new stdClass();
        $requestFactory = static fn(): object => new stdClass();
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $configuredCalls = [];

        $pager = (new pager_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            PagerServiceFactoryProbe::class,
            $block,
            $entityFactory,
            $tabFactory,
            $requestFactory,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(PagerServiceFactoryProbe::class, $pager);
        $this->assertSame($block, $pager->block);
        $this->assertSame($entityFactory, $pager->entityFactory);
        $this->assertSame($tabFactory, $pager->tabFactory);
        $this->assertSame($requestFactory, $pager->requestFactory);
        $this->assertSame($runtime, $pager->serviceBootstrapRuntime);
        $this->assertSame($configurator, $pager->serviceConfigurator);
        $this->assertSame($cacheFactory, $pager->serviceCacheFactory);
        $this->assertSame(PagerServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([
            $block,
            $entityFactory,
            $tabFactory,
            $requestFactory,
            $runtime,
            $configurator,
            $cacheFactory,
        ], $configuredCalls[0][1] ?? null);
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

final class PagerServiceFactoryProbe
{
    public function __construct(
        public base $block,
        public $entityFactory,
        public $tabFactory,
        public $requestFactory,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory
    ) {
    }
}

final class PagerServiceFactoryBlockDouble extends base
{
    public function __construct()
    {
    }

    public function getBlockName(): string
    {
        return 'pager_service_factory_block';
    }
}

final class PagerServiceFactoryRuntimeDouble
{
    public PagerServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new PagerServiceFactoryInitializerDouble();
    }

    public function getInitializer(): PagerServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    public function classNameResolver(): callable
    {
        return static fn(object|string $object): ?string => get_class_name($object);
    }
}

final class PagerServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class PagerServiceFactoryConfiguratorDouble
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

final class PagerServiceFactoryConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}



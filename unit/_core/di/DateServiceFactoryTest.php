<?php

declare(strict_types=1);

use fan\core\di\date_service_factory;
use fan\core\service\date;
use PHPUnit\Framework\TestCase;

final class DateServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreDateServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $date = new DateTime('2026-05-26 12:34:56', new DateTimeZone('UTC'));
        $state = new stdClass();
        $dateFactory = static fn(): object => new stdClass();
        $runtime = new DateServiceFactoryRuntimeDouble();
        $config = new DateServiceFactoryConfigDouble();
        $configurator = new DateServiceFactoryConfiguratorDouble($config);
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $classNameResolver = static fn(object $object): string => get_class($object);
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $overrideCalls = [];

        $service = (new date_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            date::class,
            $date,
            'mysql',
            true,
            'UTC',
            false,
            $state,
            $dateFactory,
            $runtime,
            $configurator,
            $cacheFactory,
            $classNameResolver,
            $arrayValueReader
        );

        $this->assertInstanceOf(date::class, $service);
        $this->assertSame('2026-05-26 12:34:56', $service->getCustom('Y-m-d H:i:s'));
        $this->assertTrue($service->isTime());
        $this->assertSame([], $overrideCalls);
        $this->assertSame([date::class], $runtime->initializer->serviceParams);
        $this->assertSame([$service], $configurator->getServiceConfigCalls);
        $this->assertContains($configurator->resetCalls[0][0] ?? null, ['date', date::class]);
        $this->assertSame('ENABLED', $configurator->resetCalls[0][1] ?? null);
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $date = new DateTime('2026-05-26 12:34:56', new DateTimeZone('UTC'));
        $state = new stdClass();
        $dateFactory = static fn(): object => new stdClass();
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $classNameResolver = static fn(object $object): string => get_class($object);
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $configuredCalls = [];

        $service = (new date_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            DateServiceFactoryProbe::class,
            $date,
            'mysql',
            true,
            'UTC',
            false,
            $state,
            $dateFactory,
            $runtime,
            $configurator,
            $cacheFactory,
            $classNameResolver,
            $arrayValueReader
        );

        $this->assertInstanceOf(DateServiceFactoryProbe::class, $service);
        $this->assertSame($date, $service->date);
        $this->assertSame('mysql', $service->format);
        $this->assertTrue($service->isTime);
        $this->assertSame('UTC', $service->timezone);
        $this->assertFalse($service->save);
        $this->assertSame($state, $service->state);
        $this->assertSame($dateFactory, $service->dateFactory);
        $this->assertSame($runtime, $service->serviceBootstrapRuntime);
        $this->assertSame($configurator, $service->serviceConfigurator);
        $this->assertSame($cacheFactory, $service->serviceCacheFactory);
        $this->assertSame($classNameResolver, $service->classNameResolver);
        $this->assertSame($arrayValueReader, $service->arrayValueReader);
        $this->assertIsCallable($service->dateInstanceFactory);
        $this->assertSame(DateServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([
            $date,
            'mysql',
            true,
            'UTC',
            false,
            $state,
            $dateFactory,
            $runtime,
            $configurator,
            $cacheFactory,
            $classNameResolver,
            $arrayValueReader,
        ], array_slice($configuredCalls[0][1] ?? [], 0, 12));
        $this->assertIsCallable(($configuredCalls[0][1] ?? [])[12] ?? null);
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

final class DateServiceFactoryProbe
{
    public function __construct(
        public DateTime $date,
        public mixed $format,
        public bool $isTime,
        public mixed $timezone,
        public bool $save,
        public ?object $state,
        public $dateFactory,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory,
        public $classNameResolver = null,
        public $arrayValueReader = null,
        public $dateInstanceFactory = null
    ) {
    }
}


final class DateServiceFactoryRuntimeDouble
{
    public DateServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new DateServiceFactoryInitializerDouble();
    }

    public function getInitializer(): DateServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }
}

final class DateServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class DateServiceFactoryConfiguratorDouble
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

final class DateServiceFactoryConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}

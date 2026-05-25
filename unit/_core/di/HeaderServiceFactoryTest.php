<?php

declare(strict_types=1);

use fan\core\di\header_service_factory;
use fan\core\service\header;
use PHPUnit\Framework\TestCase;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\project\service\header as service_header;

final class HeaderServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreHeaderServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $input = new HeaderServiceFactoryInputDouble();
        $headerWriter = new HeaderServiceFactoryWriterDouble();
        $runtime = new HeaderServiceFactoryRuntimeDouble();
        $config = new HeaderServiceFactoryConfigDouble();
        $configurator = new HeaderServiceFactoryConfiguratorDouble($config);
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $recursiveMerger = static fn(array $current, array $loaded): array => $current + $loaded;
        $overrideCalls = [];

        $header = (new header_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            },
            $recursiveMerger
        ))(
            header::class,
            true,
            $input,
            $headerWriter,
            $runtime,
            $configurator,
            $cacheFactory,
            $recursiveMerger
        );

        $this->assertInstanceOf(header::class, $header);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([header::class], $runtime->initializer->serviceParams);
        $this->assertSame([$header], $configurator->getServiceConfigCalls);
        $this->assertContains($configurator->resetCalls[0][0] ?? null, ['header', header::class]);
        $this->assertSame('ENABLED', $configurator->resetCalls[0][1] ?? null);
        $this->assertSame('HTTP/1.1', $header->getProtocol());
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $input = new stdClass();
        $headerWriter = new stdClass();
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $recursiveMerger = static fn(array $current, array $loaded): array => $current + $loaded;
        $configuredCalls = [];

        $header = (new header_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            },
            null
        ))(HeaderServiceFactoryProbe::class, true, $input, $headerWriter, $runtime, $configurator, $cacheFactory, $recursiveMerger);

        $this->assertInstanceOf(HeaderServiceFactoryProbe::class, $header);
        $this->assertTrue($header->allowIni);
        $this->assertSame($input, $header->input);
        $this->assertSame($headerWriter, $header->headerWriter);
        $this->assertSame($runtime, $header->serviceBootstrapRuntime);
        $this->assertSame($configurator, $header->serviceConfigurator);
        $this->assertSame($cacheFactory, $header->serviceCacheFactory);
        $this->assertSame($recursiveMerger, $header->recursiveMerger);
        $this->assertSame(HeaderServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([true, $input, $headerWriter, $runtime, $configurator, $cacheFactory, $recursiveMerger], $configuredCalls[0][1] ?? null);
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

final class HeaderServiceFactoryProbe
{
    public function __construct(
        public bool $allowIni,
        public object $input,
        public object $headerWriter,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory,
        public $recursiveMerger
    ) {
    }
}

final class HeaderServiceFactoryInputDouble
{
    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $key === 'SERVER_PROTOCOL' ? 'HTTP/1.1' : $default;
    }
}

final class HeaderServiceFactoryWriterDouble
{
}

final class HeaderServiceFactoryRuntimeDouble
{
    public HeaderServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new HeaderServiceFactoryInitializerDouble();
    }

    public function getInitializer(): HeaderServiceFactoryInitializerDouble
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

final class HeaderServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class HeaderServiceFactoryConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}

final class HeaderServiceFactoryConfiguratorDouble
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



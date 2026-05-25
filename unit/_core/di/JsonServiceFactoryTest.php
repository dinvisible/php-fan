<?php

declare(strict_types=1);

use fan\core\di\json_service_factory;
use fan\core\service\json;
use PHPUnit\Framework\TestCase;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\project\service\json as service_json;

final class JsonServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreJsonServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $errorFactory = static fn(): object => new stdClass();
        $runtime = new JsonServiceFactoryRuntimeDouble();
        $config = new JsonServiceFactoryConfigDouble();
        $configurator = new JsonServiceFactoryConfiguratorDouble($config);
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $overrideCalls = [];

        $json = (new json_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            json::class,
            true,
            $errorFactory,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(json::class, $json);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([json::class], $runtime->initializer->serviceParams);
        $this->assertSame([$json], $configurator->getServiceConfigCalls);
        $this->assertContains($configurator->resetCalls[0][0] ?? null, ['json', json::class]);
        $this->assertSame('ENABLED', $configurator->resetCalls[0][1] ?? null);
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $errorFactory = static fn(): object => new stdClass();
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $configuredCalls = [];

        $json = (new json_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            JsonServiceFactoryServiceDouble::class,
            true,
            $errorFactory,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(JsonServiceFactoryServiceDouble::class, $json);
        $this->assertTrue($json->useBase64);
        $this->assertSame([$errorFactory, $runtime, $configurator, $cacheFactory], $json->dependencies);
        $this->assertSame(JsonServiceFactoryServiceDouble::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([true, $errorFactory, $runtime, $configurator, $cacheFactory], $configuredCalls[0][1] ?? null);
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

final class JsonServiceFactoryServiceDouble
{
    public array $dependencies;

    public function __construct(
        public bool $useBase64,
        callable $errorFactory,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory
    ) {
        $this->dependencies = [$errorFactory, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory];
    }
}


final class JsonServiceFactoryRuntimeDouble
{
    public JsonServiceFactoryInitializerDouble $initializer;

    private ?service_listener_state $baseServiceListenerState = null;

    private ?service_single_state $baseServiceSingleState = null;

    public function __construct()
    {
        $this->initializer = new JsonServiceFactoryInitializerDouble();
    }

    public function getInitializer(): JsonServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

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

final class JsonServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class JsonServiceFactoryConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}

final class JsonServiceFactoryConfiguratorDouble
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

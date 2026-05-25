<?php

declare(strict_types=1);

use fan\core\di\rest_service_factory;
use fan\core\service\rest;
use PHPUnit\Framework\TestCase;

final class RestServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreRestServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $jsonFactory = static fn(): object => new stdClass();
        $curlFactory = static fn(string $url): object => (object)['url' => $url];
        $errorFactory = static fn(): object => new stdClass();
        $runtime = new RestServiceFactoryRuntimeDouble();
        $config = new RestServiceFactoryConfigDouble([
            'DEFAULT_CONNECTION' => 'main',
            'CONNECTION' => [
                'main' => [
                    'url' => [
                        'server' => 'example.test',
                        'request' => 'api',
                    ],
                ],
            ],
        ]);
        $configurator = new RestServiceFactoryConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $cacheFactory = static function (string $type) use (&$cacheFactoryCalls): object {
            $cacheFactoryCalls[] = $type;

            return (object)['type' => $type];
        };
        $overrideCalls = [];

        $rest = (new rest_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            rest::class,
            null,
            $jsonFactory,
            $curlFactory,
            $errorFactory,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(rest::class, $rest);
        $this->assertSame('main', $rest->getConnectionName());
        $this->assertSame([], $overrideCalls);
        $this->assertSame([], $runtime->initializer->serviceParams);
        $this->assertSame([$rest], $configurator->getServiceConfigCalls);
        $this->assertContains($configurator->resetCalls[0][0] ?? null, ['rest', rest::class]);
        $this->assertSame('ENABLED', $configurator->resetCalls[0][1] ?? null);
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $jsonFactory = static fn(): object => new stdClass();
        $curlFactory = static fn(string $url): object => (object)['url' => $url];
        $errorFactory = static fn(): object => new stdClass();
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $configuredCalls = [];

        $rest = (new rest_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            RestServiceFactoryProbe::class,
            'main',
            $jsonFactory,
            $curlFactory,
            $errorFactory,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(RestServiceFactoryProbe::class, $rest);
        $this->assertSame('main', $rest->connectionName);
        $this->assertSame($jsonFactory, $rest->jsonFactory);
        $this->assertSame($curlFactory, $rest->curlFactory);
        $this->assertSame($errorFactory, $rest->errorFactory);
        $this->assertSame($runtime, $rest->serviceBootstrapRuntime);
        $this->assertSame($configurator, $rest->serviceConfigurator);
        $this->assertSame($cacheFactory, $rest->serviceCacheFactory);
        $this->assertSame(RestServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([
            'main',
            $jsonFactory,
            $curlFactory,
            $errorFactory,
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

final class RestServiceFactoryProbe
{
    public function __construct(
        public ?string $connectionName,
        public $jsonFactory,
        public $curlFactory,
        public $errorFactory,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory
    ) {
    }
}


final class RestServiceFactoryRuntimeDouble
{
    public RestServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new RestServiceFactoryInitializerDouble();
    }

    public function getInitializer(): RestServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    public function classNameResolver(): callable
    {
        return static fn(object|string $object): string => is_object($object) ? get_class($object) : $object;
    }
}

final class RestServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class RestServiceFactoryConfiguratorDouble
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

final class RestServiceFactoryConfigDouble extends ArrayObject
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this : ($this[$key] ?? $default);
    }
}

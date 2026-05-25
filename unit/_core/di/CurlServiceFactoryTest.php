<?php

declare(strict_types=1);

use fan\core\di\curl_service_factory;
use fan\core\service\curl;
use PHPUnit\Framework\TestCase;
use fan\project\service\curl as service_curl;

final class CurlServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreCurlServiceWithoutDynamicOverrideFactory(): void
    {
        $state = new CurlServiceFactoryStateDouble();
        $runtime = new CurlServiceFactoryRuntimeDouble();
        $config = new CurlServiceFactoryConfigDouble([
            'CURLOPT_PROXY' => null,
            'CURLOPT_PROXYUSERPWD' => null,
        ]);
        $configurator = new CurlServiceFactoryConfiguratorDouble($config);
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $curlAdapter = new CurlServiceFactoryAdapterDouble();
        $arrayAdducer = static fn(mixed $value): array => is_array($value) ? $value : [$value];
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $overrideCalls = [];

        $curl = (new curl_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            curl::class,
            'https://example.test/api',
            'primary',
            $state,
            $runtime,
            $configurator,
            $cacheFactory,
            $curlAdapter,
            $arrayAdducer,
            $arrayValueReader
        );

        $this->assertInstanceOf(curl::class, $curl);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([curl::class], $runtime->initializer->serviceParams);
        $this->assertSame([$curl], $configurator->getServiceConfigCalls);
        $this->assertContains($configurator->resetCalls[0][0] ?? null, ['curl', curl::class]);
        $this->assertSame('ENABLED', $configurator->resetCalls[0][1] ?? null);
        $this->assertSame('https://example.test/api', $this->propertyValue($curl, 'url'));
        $this->assertSame('primary', $this->propertyValue($curl, 'index'));

        $curl->close();
        $this->assertSame([['primary', 'https://example.test/api']], $state->removed);
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $state = new stdClass();
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $curlAdapter = new stdClass();
        $arrayAdducer = static fn(mixed $value): array => is_array($value) ? $value : [$value];
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $configuredCalls = [];

        $curl = (new curl_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            CurlServiceFactoryProbe::class,
            'https://example.test/api',
            'primary',
            $state,
            $runtime,
            $configurator,
            $cacheFactory,
            $curlAdapter,
            $arrayAdducer,
            $arrayValueReader
        );

        $this->assertInstanceOf(CurlServiceFactoryProbe::class, $curl);
        $this->assertSame('https://example.test/api', $curl->url);
        $this->assertSame('primary', $curl->index);
        $this->assertSame($state, $curl->state);
        $this->assertSame($runtime, $curl->serviceBootstrapRuntime);
        $this->assertSame($configurator, $curl->serviceConfigurator);
        $this->assertSame($cacheFactory, $curl->serviceCacheFactory);
        $this->assertSame(CurlServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame(['https://example.test/api', 'primary', $state, $runtime, $configurator, $cacheFactory], $configuredCalls[0][1] ?? null);
    }

                private function propertyValue(object $object, string $propertyName): mixed
    {
        $property = new ReflectionProperty($object, $propertyName);

        return $property->getValue($object);
    }

}

final class CurlServiceFactoryProbe
{
    public function __construct(
        public string $url,
        public int|float|string $index,
        public object $state,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory
    ) {
    }
}


final class CurlServiceFactoryAdapterDouble
{
    public array $closed = [];

    public function init(string $url): object
    {
        return (object)['url' => $url];
    }

    public function setOption(object $handle, int $key, mixed $value): bool
    {
        return true;
    }

    public function close(object $handle): void
    {
        $this->closed[] = $handle;
    }

    public function getInfo(object $handle, int|float|null $option = null): mixed
    {
        return $option === null ? [] : null;
    }

    public function error(object $handle): string
    {
        return '';
    }

    public function exec(object $handle): string|bool
    {
        return false;
    }
}

final class CurlServiceFactoryStateDouble
{
    public array $removed = [];

    public function removeInstance(int|float|string $index, string $url): void
    {
        $this->removed[] = [$index, $url];
    }
}

final class CurlServiceFactoryRuntimeDouble
{
    public CurlServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new CurlServiceFactoryInitializerDouble();
    }

    public function getInitializer(): CurlServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    public function classNameResolver(): callable
    {
        return static function (string|object $object): string {
            if (is_object($object)) {
                $object = get_class($object);
            }
            $parts = explode('\\', $object);

            return (string)end($parts);
        };
    }

    public function arrayValueReader(): callable
    {
        return static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
    }
}

final class CurlServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class CurlServiceFactoryConfigDouble extends ArrayObject
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

final class CurlServiceFactoryConfiguratorDouble
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

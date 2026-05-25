<?php

declare(strict_types=1);

use fan\core\di\soap_service_factory;
use fan\core\service\soap;
use PHPUnit\Framework\TestCase;

final class SoapServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreSoapServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $errorFactory = static fn(): object => new stdClass();
        $runtime = new stdClass();
        $serviceRuntime = new SoapServiceFactoryRuntimeDouble();
        $config = new SoapServiceFactoryConfigDouble([
            'CACHE_ENABLED' => false,
            'TRACE_ENABLED' => false,
            'PARAM' => [],
        ]);
        $configurator = new SoapServiceFactoryConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $cacheFactory = static function (string $type) use (&$cacheFactoryCalls): object {
            $cacheFactoryCalls[] = $type;

            return (object)['type' => $type];
        };
        $settings = new SoapServiceFactoryRuntimeSettingsDouble();
        $wsdlFileStorage = new stdClass();
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $classNameResolver = static fn(object $object): string => get_class($object);
        $overrideCalls = [];

        $service = (new soap_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            soap::class,
            true,
            $errorFactory,
            $runtime,
            $serviceRuntime,
            $configurator,
            $cacheFactory,
            $settings,
            $wsdlFileStorage,
            $arrayValueReader,
            $classNameResolver
        );

        $this->assertInstanceOf(soap::class, $service);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([], $serviceRuntime->initializer->serviceParams);
        $this->assertSame([$service], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [soap::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame([
            ['soap.wsdl_cache_enabled', '0'],
        ], $settings->setCalls);
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $errorFactory = static fn(): object => new stdClass();
        $runtime = new stdClass();
        $serviceRuntime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $settings = new stdClass();
        $wsdlFileStorage = new stdClass();
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $classNameResolver = static fn(object $object): string => get_class($object);
        $soapHeaderFactory = null;
        $soapClientFactory = null;
        $soapVarFactory = null;
        $domDocumentFactory = null;
        $configuredCalls = [];

        $soap = (new soap_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            SoapServiceFactoryProbe::class,
            true,
            $errorFactory,
            $runtime,
            $serviceRuntime,
            $configurator,
            $cacheFactory,
            $settings,
            $wsdlFileStorage,
            $arrayValueReader,
            $classNameResolver
        );

        $this->assertInstanceOf(SoapServiceFactoryProbe::class, $soap);
        $this->assertTrue($soap->logEnabled);
        $this->assertSame($errorFactory, $soap->errorFactory);
        $this->assertSame($runtime, $soap->runtime);
        $this->assertSame($serviceRuntime, $soap->serviceBootstrapRuntime);
        $this->assertSame($configurator, $soap->serviceConfigurator);
        $this->assertSame($cacheFactory, $soap->serviceCacheFactory);
        $this->assertSame($settings, $soap->phpRuntimeSettings);
        $this->assertSame($wsdlFileStorage, $soap->wsdlFileStorage);
        $this->assertSame($arrayValueReader, $soap->arrayValueReader);
        $this->assertSame($classNameResolver, $soap->classNameResolver);
        $this->assertInstanceOf(Closure::class, $soap->soapHeaderFactory);
        $this->assertInstanceOf(Closure::class, $soap->soapClientFactory);
        $this->assertInstanceOf(Closure::class, $soap->soapVarFactory);
        $this->assertInstanceOf(Closure::class, $soap->domDocumentFactory);
        $this->assertInstanceOf(Closure::class, $soap->streamContextFactory);
        $this->assertSame(SoapServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([
            true,
            $errorFactory,
            $runtime,
            $serviceRuntime,
            $configurator,
            $cacheFactory,
            $settings,
            $wsdlFileStorage,
            $arrayValueReader,
            $classNameResolver,
        ], array_slice($configuredCalls[0][1] ?? [], 0, 10));
        $this->assertInstanceOf(Closure::class, ($configuredCalls[0][1] ?? [])[10] ?? null);
        $this->assertInstanceOf(Closure::class, ($configuredCalls[0][1] ?? [])[11] ?? null);
        $this->assertInstanceOf(Closure::class, ($configuredCalls[0][1] ?? [])[12] ?? null);
        $this->assertInstanceOf(Closure::class, ($configuredCalls[0][1] ?? [])[13] ?? null);
        $this->assertInstanceOf(Closure::class, ($configuredCalls[0][1] ?? [])[14] ?? null);
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

final class SoapServiceFactoryProbe
{
    public function __construct(
        public bool $logEnabled,
        public $errorFactory,
        public object $runtime,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory,
        public object $phpRuntimeSettings,
        public object $wsdlFileStorage,
        public $arrayValueReader,
        public $classNameResolver,
        public $soapHeaderFactory,
        public $soapClientFactory,
        public $soapVarFactory,
        public $domDocumentFactory,
        public $streamContextFactory
    ) {
    }
}


final class SoapServiceFactoryRuntimeDouble
{
    public SoapServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new SoapServiceFactoryInitializerDouble();
    }

    public function getInitializer(): SoapServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }
}

final class SoapServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class SoapServiceFactoryConfiguratorDouble
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

final class SoapServiceFactoryConfigDouble implements ArrayAccess
{
    public function __construct(private array $data)
    {
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this;
        }

        return $this->data[$key] ?? $default;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }
}

final class SoapServiceFactoryRuntimeSettingsDouble
{
    public array $setCalls = [];

    public function set(string $key, string $value): void
    {
        $this->setCalls[] = [$key, $value];
    }
}

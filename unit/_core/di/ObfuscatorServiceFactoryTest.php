<?php

declare(strict_types=1);

use fan\core\di\obfuscator_service_factory;
use fan\core\service\obfuscator;
use PHPUnit\Framework\TestCase;

final class ObfuscatorServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreObfuscatorServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $runtime = new ObfuscatorServiceFactoryRuntimeDouble();
        $config = new ObfuscatorServiceFactoryConfigDouble();
        $configurator = new ObfuscatorServiceFactoryConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $cacheFactory = static function (string $type) use (&$cacheFactoryCalls): object {
            $cacheFactoryCalls[] = $type;

            return (object)['type' => $type];
        };
        $phpArrayFileLoader = static fn(string $path, mixed $default = null): mixed => $default;
        $fileStorage = new ObfuscatorServiceFactoryFileStorageDouble();
        $overrideCalls = [];

        $obfuscator = (new obfuscator_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            obfuscator::class,
            'css',
            $runtime,
            $configurator,
            $cacheFactory,
            $phpArrayFileLoader,
            $fileStorage
        );

        $this->assertInstanceOf(obfuscator::class, $obfuscator);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([obfuscator::class], $runtime->initializer->serviceParams);
        $this->assertSame([$obfuscator], $configurator->getServiceConfigCalls);
        $this->assertSame([
            ['obfuscator', ['css', 'ENABLED']],
        ], $configurator->resetCalls);
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $phpArrayFileLoader = static fn(string $path, mixed $default = null): mixed => $default;
        $fileStorage = new stdClass();
        $configuredCalls = [];

        $obfuscator = (new obfuscator_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            ObfuscatorServiceFactoryProbe::class,
            'css',
            $runtime,
            $configurator,
            $cacheFactory,
            $phpArrayFileLoader,
            $fileStorage
        );

        $this->assertInstanceOf(ObfuscatorServiceFactoryProbe::class, $obfuscator);
        $this->assertSame('css', $obfuscator->type);
        $this->assertSame($runtime, $obfuscator->serviceBootstrapRuntime);
        $this->assertSame($configurator, $obfuscator->serviceConfigurator);
        $this->assertSame($cacheFactory, $obfuscator->serviceCacheFactory);
        $this->assertSame($phpArrayFileLoader, $obfuscator->phpArrayFileLoader);
        $this->assertSame($fileStorage, $obfuscator->fileStorage);
        $this->assertSame(ObfuscatorServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([
            'css',
            $runtime,
            $configurator,
            $cacheFactory,
            $phpArrayFileLoader,
            $fileStorage,
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

final class ObfuscatorServiceFactoryProbe
{
    public function __construct(
        public string $type,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory,
        public $phpArrayFileLoader,
        public object $fileStorage
    ) {
    }
}

final class ObfuscatorServiceFactoryFileStorageDouble
{
    public function isDirectory(string $path): bool
    {
        return true;
    }
}

final class ObfuscatorServiceFactoryRuntimeDouble
{
    public ObfuscatorServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ObfuscatorServiceFactoryInitializerDouble();
    }

    public function getInitializer(): ObfuscatorServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    public function parsePath(string $path): string
    {
        return $path;
    }
}

final class ObfuscatorServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ObfuscatorServiceFactoryConfiguratorDouble
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

final class ObfuscatorServiceFactoryConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}



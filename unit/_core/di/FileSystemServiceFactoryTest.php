<?php

declare(strict_types=1);

use fan\core\di\file_system_service_factory;
use fan\core\service\file_system;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\file_system_storage;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\project\service\file_system as service_file_system;

final class FileSystemServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreFileSystemServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();
        $filePath = tempnam(sys_get_temp_dir(), 'php-fan-fs-');
        $this->assertIsString($filePath);
        file_put_contents($filePath, "row\n");

        $runtime = new FileSystemServiceFactoryRuntimeDouble();
        $config = new FileSystemServiceFactoryConfigDouble();
        $configurator = new FileSystemServiceFactoryConfiguratorDouble($config);
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $storage = new file_system_storage();
        $overrideCalls = [];

        try {
            $service = (new file_system_service_factory(
                static function (string $className, array $arguments) use (&$overrideCalls): object {
                    $overrideCalls[] = [$className, $arguments];

                    return new stdClass();
                }
            ))(
                file_system::class,
                $filePath,
                $storage,
                $runtime,
                $configurator,
                $cacheFactory
            );

            $this->assertInstanceOf(file_system::class, $service);
            $this->assertSame([], $overrideCalls);
            $this->assertSame([$service], $configurator->getServiceConfigCalls);
            $this->assertContains($configurator->resetCalls[0][0] ?? null, ['file_system', file_system::class]);
            $this->assertSame('ENABLED', $configurator->resetCalls[0][1] ?? null);
            $this->assertSame($filePath, $service->getFullPath());
            $this->assertTrue($service->isFile());
        } finally {
            unlink($filePath);
        }
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $storage = new stdClass();
        $configuredCalls = [];

        $service = (new file_system_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            FileSystemServiceFactoryServiceDouble::class,
            '/tmp/report.txt',
            $storage,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(FileSystemServiceFactoryServiceDouble::class, $service);
        $this->assertSame('/tmp/report.txt', $service->fullPath);
        $this->assertSame([$storage, $runtime, $configurator, $cacheFactory], $service->dependencies);
        $this->assertSame(FileSystemServiceFactoryServiceDouble::class, $configuredCalls[0][0] ?? null);
        $this->assertSame(['/tmp/report.txt', $storage, $runtime, $configurator, $cacheFactory], $configuredCalls[0][1] ?? null);
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

final class FileSystemServiceFactoryServiceDouble
{
    public array $dependencies;

    public function __construct(
        public string $fullPath,
        object $storage,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory
    ) {
        $this->dependencies = [$storage, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory];
    }
}


final class FileSystemServiceFactoryRuntimeDouble
{
    private ?service_listener_state $baseServiceListenerState = null;

    private ?service_single_state $baseServiceSingleState = null;

    public function getInitializer(): FileSystemServiceFactoryInitializerDouble
    {
        return new FileSystemServiceFactoryInitializerDouble();
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

final class FileSystemServiceFactoryInitializerDouble
{
    public function setServiceParam(string $className): void
    {
    }
}

final class FileSystemServiceFactoryConfigDouble extends ArrayObject
{
    public function __construct()
    {
        parent::__construct(['ENABLED' => true]);
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this;
        }

        return array_key_exists($key, $this->getArrayCopy()) ? $this[$key] : $default;
    }
}

final class FileSystemServiceFactoryConfiguratorDouble
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

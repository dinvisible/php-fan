<?php

declare(strict_types=1);

use fan\core\service\file_system;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\adapter\file_system_storage;
use fan\core\base\service;


class ServiceFileSystemTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/file_system.php';

    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        $this->tempFiles = [];

        parent::tearDown();
    }

    public function testFileStateAccessorsReflectInjectedPath(): void
    {
        $path = $this->makeTempFile("a\nb\n");
        $service = $this->fileSystem($path);

        $this->assertTrue($service->isFile());
        $this->assertTrue($service->isRreadable());
        $this->assertSame($path, $service->getFullPath());
    }

    public function testReadByPartReturnsRowsAcrossMultipleReads(): void
    {
        $path = $this->makeTempFile("one\ntwo\nthree\n");
        $service = $this->fileSystem($path, [
            'APPROX_ROW_LENGTH' => 4,
            'PART_SIZE' => 8,
        ]);

        $this->assertSame($service, $service->setReadByPart(2));
        $this->assertSame(['one', 'two'], $service->getPartAsString());
        $this->assertSame(['three', ''], $service->getPartAsString());
        $this->assertNull($service->getPartAsString());
    }

    public function testPartAsArraySplitsRowsByConfiguredColumnSeparator(): void
    {
        $path = $this->makeTempFile("a,b\nc,d\n");
        $service = $this->fileSystem($path);

        $service->setReadByPart(2, "\n", ',');

        $this->assertSame([['a', 'b'], ['c', 'd']], $service->getPartAsArray());
    }

    public function testCloseFileClearsOpenHandle(): void
    {
        $path = $this->makeTempFile('content');
        $service = $this->fileSystem($path);

        $service->openFile();
        $this->assertNotNull($service->handleValue());

        $this->assertSame($service, $service->closeFile());
        $this->assertNull($service->handleValue());
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseFunctionAliases();

        $path = $this->makeTempFile("alpha\n");
        $runtime = new ServiceFileSystemRuntimeDouble();
        $config = new ServiceFileSystemConfigDouble([]);
        $configurator = new ServiceFileSystemConfiguratorDouble($config);
        $cacheFactoryCalls = [];

        $service = new ServiceFileSystemConstructorProbe(
            $path,
            new file_system_storage(),
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            }
        );

        $this->assertSame([], $runtime->initializer->serviceParams);
        $this->assertSame([$service], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceFileSystemConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertTrue($service->isFile());
        $this->assertSame($path, $service->getFullPath());
        $this->assertSame([], $cacheFactoryCalls);
    }

    private function fileSystem(string $path, array $config = []): ServiceFileSystemProbe
    {
        $service = new ServiceFileSystemProbe();

        $properties = [
            'fullPath' => $path,
            'isFile' => true,
        ];
        foreach ($properties as $name => $value) {
            $property = new ReflectionProperty(file_system::class, $name);
            $property->setValue($service, $value);
        }
        $property = new ReflectionProperty(file_system::class, 'storage');
        $property->setValue($service, new file_system_storage());

        $property = new ReflectionProperty(service::class, 'config');
        $property->setValue($service, new ServiceFileSystemConfigDouble($config));

        return $service;
    }

    private function makeTempFile(string $content): string
    {
        $file = tempnam(sys_get_temp_dir(), 'fan_fs_');
        if ($file === false) {
            throw new RuntimeException('Cannot create temp file.');
        }
        file_put_contents($file, $content);
        $this->tempFiles[] = $file;

        return $file;
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

final class ServiceFileSystemProbe extends file_system
{
    public function __construct()
    {
    }

    public function handleValue(): mixed
    {
        $property = new ReflectionProperty(file_system::class, 'handle');
        return $property->getValue($this);
    }
}

final class ServiceFileSystemConstructorProbe extends file_system
{
    public function __construct(
        string $fullPath,
        ?object $storage,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory
    )
    {
        parent::__construct($fullPath, $storage, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);
    }
}

final class ServiceFileSystemRuntimeDouble
{
    public ServiceFileSystemInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ServiceFileSystemInitializerDouble();
    }

    public function getInitializer(): ServiceFileSystemInitializerDouble
    {
        return $this->initializer;
    }

    public function parsePath(string $path): string
    {
        return $path;
    }
}

final class ServiceFileSystemInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceFileSystemConfiguratorDouble
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

final class ServiceFileSystemConfigDouble
{
    public function __construct(private array $data)
    {
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}

<?php

declare(strict_types=1);

use fan\core\service\obfuscator;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\service\config\row;


class GeneratedPendingServiceObfuscatorTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/obfuscator.php';

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseFunctionAliases();

        $runtime = new ServiceObfuscatorRuntimeDouble();
        $config = new row([]);
        $configurator = new ServiceObfuscatorConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $loadedPhpArrayFiles = [];

        $obfuscator = new ServiceObfuscatorConstructorProbe(
            'css',
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            },
            static function (string $path, mixed $default = null) use (&$loadedPhpArrayFiles): mixed {
                $loadedPhpArrayFiles[] = [$path, $default];

                return $default;
            },
            new ServiceObfuscatorFileStorageDouble()
        );

        $this->assertSame([ServiceObfuscatorConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$obfuscator], $configurator->getServiceConfigCalls);
        $this->assertSame([
            ['obfuscator', ['css', 'ENABLED']],
        ], $configurator->resetCalls);
        $this->assertFalse($obfuscator->isEnabled());
        $this->assertSame([], $cacheFactoryCalls);
        $this->assertSame([], $loadedPhpArrayFiles);
    }

    public function testSourceNoLongerUsesFilesystemFunctionsDirectly(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_file|is_readable|is_dir|mkdir|filesize|filemtime|file_get_contents|file_put_contents)\s*\(/',
            $this->sourceCode()
        );
    }

    public function testMakeFileChecksExistingMetaThroughInjectedPhpArrayLoader(): void
    {
        $this->ensureBaseFunctionAliases();
        if (!defined('fan\core\service\BASE_DIR')) {
            define('fan\core\service\BASE_DIR', dirname(__DIR__, 3));
        }

        $dir = sys_get_temp_dir() . '/fan_obfuscator_' . str_replace('.', '_', uniqid('', true));
        $contentDir = $dir . '/content';
        $metaDir = $dir . '/meta';
        mkdir($contentDir, 0777, true);
        mkdir($metaDir, 0777, true);

        try {
            $contentFile = $contentDir . '/bundle';
            $metaFile = $metaDir . '/bundle';
            file_put_contents($contentFile, 'cached');
            file_put_contents($metaFile, '<?php return [];');

            $loadedPhpArrayFiles = [];
            $obfuscator = new ServiceObfuscatorConstructorProbe(
                'css',
                new ServiceObfuscatorRuntimeDouble(),
                new ServiceObfuscatorConfiguratorDouble(new row([])),
                null,
                static function (string $path, mixed $default = null) use (&$loadedPhpArrayFiles): mixed {
                    $loadedPhpArrayFiles[] = [$path, $default];

                    return [];
                },
                new ServiceObfuscatorFileStorageDouble()
            );
            $obfuscator->setDirectories($contentDir, $metaDir);

            $this->assertSame($obfuscator, $obfuscator->exposeMakeFile(['missing.css'], 'bundle'));
            $this->assertSame([
                [$metaFile, []],
            ], $loadedPhpArrayFiles);
            $this->assertSame('cached', file_get_contents($contentFile));
        } finally {
            @unlink($contentDir . '/bundle');
            @unlink($metaDir . '/bundle');
            @rmdir($contentDir);
            @rmdir($metaDir);
            @rmdir($dir);
        }
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

final class ServiceObfuscatorConstructorProbe extends obfuscator
{
    public function __construct(
        string $type,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory,
        ?callable $phpArrayFileLoader,
        ?object $fileStorage = null
    )
    {
        parent::__construct($type, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory, $phpArrayFileLoader, $fileStorage);
    }

    public function exposeMakeFile(array $list, string $name): static
    {
        return $this->_makeFile($list, $name);
    }

    public function setDirectories(string $contentDir, string $metaDir): void
    {
        $property = new ReflectionProperty(obfuscator::class, 'contentDir');
        $property->setValue($this, $contentDir);
        $property = new ReflectionProperty(obfuscator::class, 'metaDir');
        $property->setValue($this, $metaDir);
    }
}

final class ServiceObfuscatorRuntimeDouble
{
    public ServiceObfuscatorInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ServiceObfuscatorInitializerDouble();
    }

    public function getInitializer(): ServiceObfuscatorInitializerDouble
    {
        return $this->initializer;
    }

    public function parsePath(string $path): string
    {
        return $path;
    }
}

final class ServiceObfuscatorInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceObfuscatorConfiguratorDouble
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

final class ServiceObfuscatorFileStorageDouble
{
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function makeDirectory(string $path, int $mode = 0750, bool $recursive = true): bool
    {
        return mkdir($path, $mode, $recursive);
    }

    public function size(string $path): int|false
    {
        return filesize($path);
    }

    public function modifiedTime(string $path): int|false
    {
        return filemtime($path);
    }

    public function read(string $path): string|false
    {
        return file_get_contents($path);
    }

    public function write(string $path, string $content): int|false
    {
        return file_put_contents($path, $content);
    }
}

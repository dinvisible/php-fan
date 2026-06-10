<?php

declare(strict_types=1);

use fan\core\di\config_service_factory;
use fan\core\service\config;
use fan\core\service\config_state;
use PHPUnit\Framework\TestCase;
use FanTest\core\ConfigRowFactory;
use fan\core\service\config\row;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\project\service\config as service_config;

final class ConfigServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreConfigServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $configDir = $this->makeConfigDir([
            'config' => ['ENABLED' => true],
        ]);
        $runtime = new ConfigServiceFactoryRuntimeDouble($configDir);
        $configurator = new ConfigServiceFactoryConfiguratorDouble();
        $cacheFactoryCalls = [];
        $overrideCalls = [];
        $configFactory = static fn(string $configType, string $sourceType): object => (object)[
            'configType' => $configType,
            'sourceType' => $sourceType,
        ];
        $configCacheFactory = static fn(): null => null;
        $configState = new config_state();
        $cacheFactory = static function (string $type) use (&$cacheFactoryCalls): object {
            $cacheFactoryCalls[] = $type;

            return (object)['type' => $type];
        };
        $phpArrayFileLoader = static fn(string $path, mixed $default = null): mixed => is_readable($path) ? include $path : $default;
        $shortClassNameResolver = $this->shortClassNameResolver();
        $configRowFactory = static fn(mixed $data): row => ConfigRowFactory::row(
            $data,
            shortClassNameResolver: $shortClassNameResolver
        );
        $sourceFileMetadata = new ConfigServiceFactoryFileMetadataDouble();
        $sourceFileStorage = new ConfigServiceFactorySourceFileStorageDouble();

        $config = (new config_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            },
            $shortClassNameResolver
        ))(
            config::class,
            'service',
            'arr',
            $configFactory,
            $configCacheFactory,
            $configState,
            $runtime,
            $runtime,
            $configurator,
            $cacheFactory,
            $phpArrayFileLoader,
            $configRowFactory,
            $sourceFileMetadata,
            $sourceFileStorage,
            $shortClassNameResolver
        );

        $this->assertInstanceOf(config::class, $config);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([config::class], $runtime->initializer->serviceParams);
        $this->assertSame([
            [config::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame('service', $config->getConfigType());
        $this->assertTrue($config->get('config', 'ENABLED'));
        $this->assertSame([], $cacheFactoryCalls);
        $this->assertSame($config, $configState->getInstance('service'));
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $configFactory = static fn(string $configType, string $sourceType): object => (object)[
            'configType' => $configType,
            'sourceType' => $sourceType,
        ];
        $configCacheFactory = static fn(): object => new stdClass();
        $configState = new stdClass();
        $runtime = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $phpArrayFileLoader = static fn(string $path, mixed $default = null): mixed => $default;
        $configRowFactory = static fn(mixed $data): object => (object)['data' => $data];
        $sourceFileMetadata = new stdClass();
        $sourceFileStorage = new stdClass();
        $shortClassNameResolver = $this->shortClassNameResolver();
        $configuredCalls = [];

        $config = (new config_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            },
            null
        ))(
            ConfigServiceFactoryProbe::class,
            'service',
            'arr',
            $configFactory,
            $configCacheFactory,
            $configState,
            $runtime,
            $runtime,
            null,
            $cacheFactory,
            $phpArrayFileLoader,
            $configRowFactory,
            $sourceFileMetadata,
            $sourceFileStorage,
            $shortClassNameResolver
        );

        $this->assertInstanceOf(ConfigServiceFactoryProbe::class, $config);
        $this->assertSame('service', $config->configType);
        $this->assertSame('arr', $config->sourceType);
        $this->assertSame($configFactory, $config->configFactory);
        $this->assertSame($configCacheFactory, $config->configCacheFactory);
        $this->assertSame($configState, $config->configState);
        $this->assertSame($runtime, $config->runtime);
        $this->assertSame($runtime, $config->serviceBootstrapRuntime);
        $this->assertNull($config->serviceConfigurator);
        $this->assertSame($cacheFactory, $config->serviceCacheFactory);
        $this->assertSame($phpArrayFileLoader, $config->phpArrayFileLoader);
        $this->assertSame($configRowFactory, $config->configRowFactory);
        $this->assertSame($sourceFileMetadata, $config->sourceFileMetadata);
        $this->assertSame($sourceFileStorage, $config->sourceFileStorage);
        $this->assertSame($shortClassNameResolver, $config->shortClassNameResolver);
        $this->assertSame(ConfigServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([
            'service',
            'arr',
            $configFactory,
            $configCacheFactory,
            $configState,
            $runtime,
            $runtime,
            null,
            $cacheFactory,
            $phpArrayFileLoader,
            $configRowFactory,
            $sourceFileMetadata,
            $sourceFileStorage,
            $shortClassNameResolver,
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
        if (!function_exists('get_class_alt')) {
            eval('function get_class_alt(mixed $value): ?string { return is_object($value) ? get_class($value) : (is_string($value) ? $value : null); }');
        }
        if (!function_exists('fan\core\base\get_class_alt')) {
            eval('namespace fan\core\base { function get_class_alt(mixed $value): ?string { return \get_class_alt($value); } }');
        }
    }

    private function shortClassNameResolver(): callable
    {
        return static function (object|string $object): string {
            if ($object instanceof config) {
                return 'config';
            }
            $className = is_object($object) ? get_class($object) : $object;
            $parts = explode('\\', $className);

            return (string)end($parts);
        };
    }

    private function makeConfigDir(array $serviceConfig): string
    {
        $dir = sys_get_temp_dir() . '/php-fan-config-factory-test-' . bin2hex(random_bytes(4));
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/service.php', "<?php\nreturn " . var_export($serviceConfig, true) . ";\n");

        return $dir;
    }
}

final class ConfigServiceFactoryProbe
{
    public function __construct(
        public string $configType,
        public string $sourceType,
        public $configFactory,
        public $configCacheFactory,
        public object $configState,
        public object $runtime,
        public object $serviceBootstrapRuntime,
        public ?object $serviceConfigurator,
        public $serviceCacheFactory,
        public $phpArrayFileLoader,
        public $configRowFactory,
        public object $sourceFileMetadata,
        public object $sourceFileStorage,
        public $shortClassNameResolver
    ) {
    }
}


final class ConfigServiceFactoryFileMetadataDouble
{
    public function size(string $path): int|false
    {
        return is_file($path) ? filesize($path) : false;
    }
}

final class ConfigServiceFactorySourceFileStorageDouble
{
    public function exists(string $path): bool
    {
        return is_file($path);
    }
}

final class ConfigServiceFactoryRuntimeDouble
{
    public ConfigServiceFactoryInitializerDouble $initializer;

    public function __construct(private string $configDir)
    {
        $this->initializer = new ConfigServiceFactoryInitializerDouble();
    }

    public function getInitializer(): ConfigServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    public function loadClass(string $class, bool $makeAlias = true): bool
    {
        return class_exists($class);
    }

    public function serviceEngineFactory(): callable
    {
        return static fn(string $class): object => new $class();
    }

    public function getGlobalPath(string $key, ?string $altPath = null): string
    {
        return $key === 'config_source' ? $this->configDir : (string)$altPath;
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

final class ConfigServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ConfigServiceFactoryConfiguratorDouble
{
    public array $resetCalls = [];

    public function reset(string $className, string $key): void
    {
        $this->resetCalls[] = [$className, $key];
    }
}

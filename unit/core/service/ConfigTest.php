<?php

declare(strict_types=1);

use fan\core\service\config;
use fan\core\service\config_state;
use FanTest\core\SourceFileContractTestCase;
use FanTest\core\ConfigRowFactory;
use fan\core\service\config\row;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;


if (!function_exists('get_class_alt')) {
    function get_class_alt(mixed $value): string
    {
        return is_object($value) ? get_class($value) : (string)$value;
    }
}

if (!function_exists('get_class_name')) {
    function get_class_name(string|object $object): ?string
    {
        $class = is_object($object) ? get_class($object) : $object;
        $parts = explode('\\', $class);

        return array_pop($parts);
    }
}

class ServiceConfigTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/config.php';

    public function testGetSetMergeAndResetOperateOnConfigRows(): void
    {
        $config = $this->config([
            'service' => [
                'enabled' => true,
            ],
        ]);

        $this->assertTrue($config->get('service', 'enabled'));
        $this->assertSame($config, $config->set('service', 'host', 'localhost'));
        $this->assertSame('localhost', $config->get('service', 'host'));

        $this->assertSame($config, $config->merge([
            'service' => ['port' => 3306],
            'app' => ['name' => 'demo'],
        ]));
        $this->assertSame(3306, $config->get('service', 'port'));
        $this->assertSame('demo', $config->get('app', 'name'));

        $config->reset('service', 'host');
        $this->assertNull($config->get('service', 'host'));
    }

    public function testGetSrcReturnsOriginalSourceData(): void
    {
        $config = $this->config([
            'service' => [
                'enabled' => true,
                'nested' => ['key' => 'value'],
            ],
        ]);

        $config->set('service', 'enabled', 'changed');

        $this->assertSame([
            'enabled' => true,
            'nested' => ['key' => 'value'],
        ], $config->getSrc('service'));
        $this->assertSame(['key' => 'value'], $config->getSrc('service', 'nested'));
    }

    public function testConfigTypeIsReadFromInjectedState(): void
    {
        $config = $this->config([], 'entity');

        $this->assertSame('entity', $config->getConfigType());
    }

    public function testGetControllerConfigCreatesMissingRows(): void
    {
        $config = $this->config([]);
        $row = $config->getControllerConfig(new stdClass(), 'plain_ctrl');

        $this->assertInstanceOf(row::class, $row);
        $this->assertSame([], $row->toArray());
    }

    public function testMergeByAppUsesInjectedConfigFactory(): void
    {
        $target = new ServiceConfigTargetDouble();
        $state = new config_state();
        $state->setAppDepended([
            'route' => '{APP_NAME}/route',
        ]);
        $config = $this->config([], 'service', $state);

        $config->setConfigDependencies(
            fn(string $configType, string $sourceType = 'arr'): ServiceConfigTargetDouble => $target->for($configType, $sourceType)
        );

        $config->mergeByApp('admin');

        $this->assertSame('route', $target->configType);
        $this->assertSame('arr', $target->sourceType);
        $this->assertSame(['admin/route', true, false], $target->mergeCalls[0]);
    }

    public function testMergeByAppRequiresInjectedConfigFactory(): void
    {
        $state = new config_state();
        $state->setAppDepended([
            'route' => '{APP_NAME}/route',
        ]);
        $config = $this->config([], 'service', $state);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config factory is not configured for config service.');

        $config->mergeByApp('admin');
    }

    public function testGetServiceConfigUsesInjectedServiceExceptionFactoryWhenDataRowIsMissing(): void
    {
        $calls = [];
        $config = $this->config([]);
        $dataProperty = new ReflectionProperty(config::class, 'confData');
        $dataProperty->setValue($config, null);
        $config->setServiceDependencies(
            null,
            null,
            null,
            null,
            null,
            null,
            static function (string $exceptionClass, object $service, string $message, int $code, ?Throwable $previous = null) use (&$calls): Throwable {
                $calls[] = [$exceptionClass, $service, $message, $code, $previous];

                return new RuntimeException($message, $code, $previous);
            }
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Data row isn\'t set for config "service"');

        try {
            $config->getServiceConfig($config);
        } finally {
            $this->assertCount(1, $calls);
            $this->assertSame('\fan\project\exception\service\fatal', $calls[0][0]);
            $this->assertSame($config, $calls[0][1]);
            $this->assertSame('Data row isn\'t set for config "service"', $calls[0][2]);
            $this->assertSame(E_USER_ERROR, $calls[0][3]);
            $this->assertNull($calls[0][4]);
        }
    }

    public function testGetServiceConfigUsesInjectedShortClassNameResolver(): void
    {
        $config = $this->config([
            'InjectedServiceName' => [
                'ENABLED' => true,
            ],
        ]);

        $row = $config->getServiceConfig($config);

        $this->assertSame(['ENABLED' => true], $row->toArray());
        $this->assertSame([$config], $row->getOwners());
    }

    public function testUnknownConfigEngineUsesInjectedServiceExceptionFactory(): void
    {
        $calls = [];
        $config = $this->config([], 'service', new config_state(), 'missing');
        $runtime = new ServiceConfigRuntimeDouble(sys_get_temp_dir());
        $config->setServiceDependencies(
            $runtime,
            null,
            null,
            null,
            null,
            null,
            static function (string $exceptionClass, object $service, string $message, int $code, ?Throwable $previous = null) use (&$calls): Throwable {
                $calls[] = [$exceptionClass, $service, $message, $code, $previous];

                return new RuntimeException($message, $code, $previous);
            }
        );

        $method = new ReflectionMethod(config::class, '_getConfigEngine');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown engine type!');

        try {
            $method->invoke($config);
        } finally {
            $this->assertCount(1, $calls);
            $this->assertSame('\fan\project\exception\service\fatal', $calls[0][0]);
            $this->assertSame($config, $calls[0][1]);
            $this->assertSame('Unknown engine type!', $calls[0][2]);
            $this->assertSame(E_USER_ERROR, $calls[0][3]);
            $this->assertNull($calls[0][4]);
        }
    }

    public function testConfigCacheRequiresInjectedFactory(): void
    {
        $config = $this->config([]);
        $method = new ReflectionMethod(config::class, 'configCache');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config cache factory is not configured for config service.');

        $method->invoke($config);
    }

    public function testConfigRuntimeRequiresInjectedRuntime(): void
    {
        $config = $this->config([]);
        $method = new ReflectionMethod(config::class, 'configRuntime');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Bootstrap runtime service is not configured for config service.');

        $method->invoke($config);
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('setConfigDependencies', $source);
        $this->assertStringContainsString('$engine->setPhpArrayFileLoader($this->phpArrayFileLoader());', $source);
        $this->assertStringContainsString('$engine->setFileStorage($this->sourceFileStorage());', $source);
        $this->assertStringContainsString('$name = $this->shortClassName($service);', $source);
        $this->assertStringContainsString('$this->sourceFileMetadata()->size($filePath)', $source);
        $this->assertStringContainsString('$this->confData = $this->configRow($this->_getData', $source);
        $this->assertStringContainsString('$this->state()->setInstance($configType, $this);', $source);
        $this->assertStringContainsString('$this->createServiceFatalException(', $source);
        $this->assertStringNotContainsString('parent::__construct();', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
        $this->assertStringNotContainsString('new fatalException(', $source);
        $this->assertStringNotContainsString('new \fan\project\exception\service\fatal', $source);
        $this->assertStringNotContainsString('get_class_name(', $source);
        $this->assertStringNotContainsString('getContainerService(', $source);
        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('self::$', $source);
        $this->assertStringNotContainsString('protected static array $instances', $source);
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $configDir = $this->makeConfigDir([
            'config' => ['ENABLED' => true],
        ]);
        $runtime = new ServiceConfigRuntimeDouble($configDir);
        $configurator = new ServiceConfigConfiguratorDouble();
        $cacheFactoryCalls = [];

        $config = new config(
            'service',
            'arr',
            null,
            static fn(): null => null,
            new config_state(),
            $runtime,
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            },
            static fn(string $path, mixed $default = null): mixed => is_readable($path) ? include $path : $default,
            fn(mixed $data): row => $this->configRow($data),
            new ServiceConfigFileMetadataDouble(),
            new ServiceConfigSourceFileStorageDouble(),
            $this->shortClassNameResolver()
        );

        $this->assertSame([config::class], $runtime->initializer->serviceParams);
        $this->assertSame([
            [config::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame('service', $config->getConfigType());
        $this->assertTrue($config->get('config', 'ENABLED'));
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testConfigCacheStoresInjectedSourceFileSizeMetadata(): void
    {
        $configDir = $this->makeConfigDir([
            'config' => ['ENABLED' => true],
        ]);
        $runtime = new ServiceConfigRuntimeDouble($configDir);
        $cache = new ServiceConfigCacheDouble();
        $metadata = new ServiceConfigFileMetadataDouble(456);

        new config(
            'service',
            'arr',
            null,
            static fn(): ServiceConfigCacheDouble => $cache,
            new config_state(),
            $runtime,
            $runtime,
            new ServiceConfigConfiguratorDouble(),
            static fn(string $type): object => (object)['type' => $type],
            static fn(string $path, mixed $default = null): mixed => is_readable($path) ? include $path : $default,
            fn(mixed $data): row => $this->configRow($data),
            $metadata,
            new ServiceConfigSourceFileStorageDouble(),
            $this->shortClassNameResolver()
        );

        $configFile = $configDir . '/service.php';
        $this->assertSame([['service', null]], $cache->getCalls);
        $this->assertSame('service', $cache->setCalls[0][0] ?? null);
        $this->assertSame([['service', 'file_size', 456]], $cache->setExtraMetaCalls);
        $this->assertSame([$configFile], $metadata->sizeCalls);
    }

    public function testSourceNoLongerReadsSourceFileSizeDirectly(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\bfilesize\s*\(/',
            $this->sourceCode()
        );
    }

    private function config(array $data, string $configType = 'service', ?config_state $state = null, string $sourceType = 'arr'): config
    {
        $config = (new ReflectionClass(config::class))->newInstanceWithoutConstructor();

        $dataProperty = new ReflectionProperty(config::class, 'confData');
        $dataProperty->setValue($config, $this->configRow($data));

        $typeProperty = new ReflectionProperty(config::class, 'configType');
        $typeProperty->setValue($config, $configType);

        $sourceTypeProperty = new ReflectionProperty(config::class, 'sourceType');
        $sourceTypeProperty->setValue($config, $sourceType);

        $stateProperty = new ReflectionProperty(config::class, 'configState');
        $stateProperty->setValue($config, $state ?? new config_state());

        $shortClassNameResolverProperty = new ReflectionProperty(config::class, 'shortClassNameResolver');
        $shortClassNameResolverProperty->setValue($config, Closure::fromCallable($this->shortClassNameResolver()));

        return $config;
    }

    private function configRow(mixed $data): row
    {
        return ConfigRowFactory::row(
            $data,
            shortClassNameResolver: $this->shortClassNameResolver()
        );
    }

    private function shortClassNameResolver(): callable
    {
        return static function (object|string $object): string {
            if ($object instanceof config) {
                return 'InjectedServiceName';
            }
            $className = is_object($object) ? get_class($object) : $object;
            $parts = explode('\\', $className);

            return (string)end($parts);
        };
    }

    private function makeConfigDir(array $serviceConfig): string
    {
        $dir = sys_get_temp_dir() . '/php-fan-config-test-' . bin2hex(random_bytes(4));
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/service.php', "<?php\nreturn " . var_export($serviceConfig, true) . ";\n");

        return $dir;
    }

}

final class ServiceConfigTargetDouble
{
    public ?string $configType = null;
    public ?string $sourceType = null;
    public array $mergeCalls = [];

    public function for(string $configType, string $sourceType): self
    {
        $this->configType = $configType;
        $this->sourceType = $sourceType;

        return $this;
    }

    public function _mergeConfig(string $fileName, bool $resetConf, bool $checkExist): void
    {
        $this->mergeCalls[] = [$fileName, $resetConf, $checkExist];
    }
}

final class ServiceConfigRuntimeDouble
{
    public ServiceConfigInitializerDouble $initializer;
    public array $loadClassCalls = [];

    public function __construct(private string $configDir)
    {
        $this->initializer = new ServiceConfigInitializerDouble();
    }

    public function getInitializer(): ServiceConfigInitializerDouble
    {
        return $this->initializer;
    }

    public function loadClass(string $class, bool $makeAlias = true): bool
    {
        $this->loadClassCalls[] = [$class, $makeAlias];

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

final class ServiceConfigInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceConfigConfiguratorDouble
{
    public array $resetCalls = [];

    public function reset(string $className, string $key): void
    {
        $this->resetCalls[] = [$className, $key];
    }
}

final class ServiceConfigCacheDouble
{
    public array $getCalls = [];

    public array $setCalls = [];

    public array $setExtraMetaCalls = [];

    public function get(string $key, mixed $default = null): mixed
    {
        $this->getCalls[] = [$key, $default];

        return $default;
    }

    public function checkSourceFile(string $key, string $filePath): bool
    {
        return false;
    }

    public function set(string $key, mixed $data): static
    {
        $this->setCalls[] = [$key, $data];

        return $this;
    }

    public function setExtraMeta(string $key, string $name, mixed $value): static
    {
        $this->setExtraMetaCalls[] = [$key, $name, $value];

        return $this;
    }
}

final class ServiceConfigFileMetadataDouble
{
    public array $sizeCalls = [];

    public function __construct(private int|false $size = 1)
    {
    }

    public function size(string $path): int|false
    {
        $this->sizeCalls[] = $path;

        return $this->size;
    }
}

final class ServiceConfigSourceFileStorageDouble
{
    public array $existsCalls = [];

    public function exists(string $path): bool
    {
        $this->existsCalls[] = $path;

        return is_file($path);
    }
}

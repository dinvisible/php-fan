<?php

declare(strict_types=1);

namespace fan\core\base {
    if (!function_exists(__NAMESPACE__ . '\\get_class_name')) {
        function get_class_name(string|object $object): ?string
        {
            $class = is_object($object) ? get_class($object) : $object;
            $parts = explode('\\', $class);

            return array_pop($parts);
        }
    }
}

namespace {
use fan\core\service\cache;
use fan\core\service\cache\base as CacheEngineBase;
use FanTest\core\SourceFileContractTestCase;

class ServiceCacheTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/cache.php';

    public function testGetSetSaveAndDeleteDelegateToEngineForKey(): void
    {
        $cache = new ServiceCacheProbe();

        $this->assertSame('fallback', $cache->get('home', 'fallback'));
        $this->assertSame($cache, $cache->set('home', 'payload', false));
        $this->assertSame('payload', $cache->get('home'));
        $this->assertFalse($cache->isSaved('home'));

        $this->assertSame($cache, $cache->save('home'));
        $this->assertTrue($cache->isSaved('home'));

        $this->assertSame($cache, $cache->delete('home'));
        $this->assertSame('fallback', $cache->get('home', 'fallback'));
    }

    public function testGetOrDefineComputesAndStoresValueOnlyWhenEngineIsNotLoaded(): void
    {
        $cache = new ServiceCacheProbe();

        $value = $cache->getOrDefine('profile', static fn (string $key): array => ['key' => $key], false);

        $this->assertSame(['key' => 'profile'], $value);
        $this->assertSame(['key' => 'profile'], $cache->get('profile'));

        $second = $cache->getOrDefine('profile', static fn (): string => 'new', false);
        $this->assertSame(['key' => 'profile'], $second);
    }

    public function testGetOrDefineInvalidCallbackUsesInjectedExceptionFactory(): void
    {
        $factoryCalls = [];
        $cache = new ServiceCacheProbe();
        $cache->setServiceDependencies(
            serviceExceptionFactory: static function (
                string $exceptionClass,
                \fan\core\base\service $service,
                string $message,
                int $code,
                ?Throwable $previous = null
            ) use (&$factoryCalls): Throwable {
                $factoryCalls[] = [$exceptionClass, $service, $message, $code, $previous];

                return new RuntimeException($message, $code, $previous);
            }
        );

        try {
            $cache->getOrDefine('profile', 'not-a-callback', false);
            $this->fail('Expected cache service to use injected service exception factory.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Callback for cache is not callable.', $exception->getMessage());
        }

        $this->assertCount(1, $factoryCalls);
        $this->assertSame('\fan\project\exception\service\fatal', $factoryCalls[0][0]);
        $this->assertSame($cache, $factoryCalls[0][1]);
        $this->assertSame(E_USER_ERROR, $factoryCalls[0][3]);
        $this->assertNull($factoryCalls[0][4]);
    }

    public function testExtraMetaComparisonCanDeleteStaleCache(): void
    {
        $cache = new ServiceCacheProbe();

        $cache->set('asset', 'content', false);
        $cache->setExtraMeta('asset', 'etag', 'abc');

        $this->assertFalse($cache->checkExtraMeta('asset', 'etag', 'def', 'equal', true));
        $this->assertSame('missing', $cache->get('asset', 'missing'));
    }

    public function testSetLifetimeAndExtraPathDelegateToEngine(): void
    {
        $cache = new ServiceCacheProbe();

        $this->assertSame($cache, $cache->set('fragment', 'html'));
        $this->assertSame($cache, $cache->setLifetime('fragment', 60));
        $this->assertSame($cache, $cache->setExtraPath('fragment', 'nested'));

        $engine = $cache->engine('fragment');
        $this->assertSame(60, $engine->getMeta(false)['lifetime']);
        $this->assertSame('nested', $engine->extraPath());
    }

    public function testConfigCacheUsesInjectedRuntimeConfigCache(): void
    {
        $state = new \fan\core\service\cache_state();
        $runtime = new ServiceCacheRuntimeDouble(['service' => ['cached' => true]]);
        $cache = new cache(
            cache::CONFIG_TYPE,
            $runtime,
            null,
            $state,
            new \fan\core\service\cache_memcache_state()
        );

        $this->assertInstanceOf(cache::class, $cache);
        $this->assertSame($cache, $state->getInstance(cache::CONFIG_TYPE));
        $this->assertSame(1, $runtime->calls);
    }

    public function testConfigCacheBootstrapFailureUsesInjectedExceptionFactory(): void
    {
        $state = new \fan\core\service\cache_state();
        $runtime = new ServiceCacheRuntimeDouble([]);
        $factoryCalls = [];
        $exceptionFactory = static function (
            string $message,
            int $code = E_USER_ERROR,
            ?Throwable $previous = null
        ) use (&$factoryCalls): Throwable {
            $factoryCalls[] = [$message, $code, $previous];

            return new RuntimeException($message, $code, $previous);
        };

        try {
            new cache(
                cache::CONFIG_TYPE,
                $runtime,
                null,
                $state,
                new \fan\core\service\cache_memcache_state(),
                null,
                null,
                null,
                null,
                null,
                $exceptionFactory
            );
            $this->fail('Expected config cache bootstrap failure to use injected exception factory.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Config Cache in bootstrap isn\'t defined.', $exception->getMessage());
            $this->assertSame(E_USER_ERROR, $exception->getCode());
        }

        $this->assertSame([
            ['Config Cache in bootstrap isn\'t defined.', E_USER_ERROR, null],
        ], $factoryCalls);
        $this->assertSame(1, $runtime->calls);
        $this->assertNull($state->getInstance(cache::CONFIG_TYPE));
    }

    public function testConfigCacheEngineFailureUsesInjectedExceptionFactory(): void
    {
        $state = new \fan\core\service\cache_state();
        $runtime = new ServiceCacheRuntimeDouble(['service' => ['LIFETIME' => 10]]);
        $factoryCalls = [];
        $exceptionFactory = static function (
            string $message,
            int $code = E_USER_ERROR,
            ?Throwable $previous = null
        ) use (&$factoryCalls): Throwable {
            $factoryCalls[] = [$message, $code, $previous];

            return new RuntimeException($message, $code, $previous);
        };
        $cache = new cache(
            cache::CONFIG_TYPE,
            $runtime,
            null,
            $state,
            new \fan\core\service\cache_memcache_state(),
            static fn(): object => new stdClass(),
            null,
            null,
            null,
            null,
            $exceptionFactory
        );

        try {
            $cache->get('service');
            $this->fail('Expected config cache engine failure to use injected exception factory.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Cache engine isn\'t defined.', $exception->getMessage());
            $this->assertSame(E_USER_ERROR, $exception->getCode());
        }

        $this->assertSame([
            ['Cache engine isn\'t defined.', E_USER_ERROR, null],
        ], $factoryCalls);
    }

    public function testConstructorUsesInjectedStateAndBaseServiceDependencies(): void
    {
        $state = new \fan\core\service\cache_state();
        $runtime = new ServiceCacheRuntimeDouble([]);
        $configurator = new ServiceCacheConfiguratorDouble(new ServiceCacheConfigDouble([
            'ENABLED' => true,
        ]));
        $cacheFactoryCalls = [];

        $cache = new ServiceCacheConstructorProbe(
            'common_by_file',
            $runtime,
            static fn(): object => new stdClass(),
            $state,
            new \fan\core\service\cache_memcache_state(),
            null,
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            },
            new ServiceCacheSourceFileMetadataDouble()
        );

        $this->assertSame($cache, $state->getInstance('common_by_file'));
        $this->assertSame([ServiceCacheConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$cache], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceCacheConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testGetEngineUsesInjectedCacheEngineFactory(): void
    {
        $state = new \fan\core\service\cache_state();
        $memcacheState = new \fan\core\service\cache_memcache_state();
        $runtime = new ServiceCacheRuntimeDouble([]);
        $logger = new stdClass();
        $factoryCalls = [];
        $cache = new ServiceCacheEngineFactoryProbe(
            'common_by_file',
            $runtime,
            static fn(): object => $logger,
            $state,
            $memcacheState,
            static function (
                string $class,
                object $facade,
                string $type,
                string $key,
                array $config,
                object $errorLogger,
                object $runtime,
                ?object $memcacheState
            ) use (&$factoryCalls): object {
                $engine = new ServiceCacheEngineDouble($facade, $type, $key, $config, $errorLogger, $runtime);
                $factoryCalls[] = [$class, $facade, $type, $key, $config, $errorLogger, $runtime, $memcacheState, $engine];

                return $engine;
            },
            $runtime,
            new ServiceCacheConfiguratorDouble(new ServiceCacheConfigDouble(['ENABLED' => true])),
            static fn(string $type): object => (object)['type' => $type],
            new ServiceCacheSourceFileMetadataDouble()
        );

        $engine = $cache->getEngine('home');

        $this->assertSame($factoryCalls[0][8], $engine);
        $this->assertSame([
            '\\' . ServiceCacheEngineDouble::class,
            $cache,
            'common_by_file',
            'home',
            ['ENGINE' => 'file', 'LIFETIME' => 10],
            $logger,
            $runtime,
            null,
            $engine,
        ], $factoryCalls[0]);
        $this->assertSame($engine, $cache->getEngine('home'));
        $this->assertCount(1, $factoryCalls);
    }

    public function testCheckSourceFileUsesInjectedMetadataAdapter(): void
    {
        $metadata = new ServiceCacheSourceFileMetadataDouble(true, 7, strtotime('2026-05-31 10:11:12'));
        $cache = new ServiceCacheProbe($metadata);

        $cache->set('asset', 'content', false);
        $cache->setExtraMeta('asset', 'file_size', 7);

        $this->assertTrue($cache->checkSourceFile('asset', '/virtual/source.php'));
        $this->assertSame(['/virtual/source.php'], $metadata->isFileCalls);
        $this->assertSame(['/virtual/source.php'], $metadata->sizeCalls);
        $this->assertSame(['/virtual/source.php'], $metadata->modifiedTimeCalls);
    }

    public function testCacheServiceNoLongerKeepsInstancesInStaticProperty(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('private static array $instances', $source);
        $this->assertStringContainsString('$this->sourceFileMetadata()', $source);
        $this->assertStringContainsString('$this->createServiceFatalException(', $source);
        $this->assertStringContainsString('$this->createConfigCacheFatalException(', $source);
        $this->assertStringNotContainsString('parent::__construct(empty(self::$instances));', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('new \Exception', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
        $this->assertStringNotContainsString('new $class(...$arguments)', $source);
        $this->assertStringNotContainsString('private ?\Closure $errorFactory', $source);
        $this->assertStringNotContainsString('private ?\Closure $configCacheFatalExceptionFactory', $source);
        $this->assertStringNotContainsString('$this->errorFactory === null', $source);
        $this->assertStringNotContainsString('$this->configCacheFatalExceptionFactory === null', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_file|filesize|filemtime)\s*\(/',
            $source
        );
    }
}

final class ServiceCacheConstructorProbe extends cache
{
    public function __construct(
        string $type,
        ?object $runtime,
        ?callable $errorFactory,
        ?object $cacheState,
        ?object $memcacheState,
        ?callable $cacheEngineFactory,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory,
        ?object $sourceFileMetadata
    )
    {
        parent::__construct(
            $type,
            $runtime,
            $errorFactory,
            $cacheState,
            $memcacheState,
            $cacheEngineFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $sourceFileMetadata
        );
    }
}

final class ServiceCacheEngineFactoryProbe extends cache
{
    public function __construct(
        string $type,
        ?object $runtime,
        ?callable $errorFactory,
        ?object $cacheState,
        ?object $memcacheState,
        ?callable $cacheEngineFactory,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory,
        ?object $sourceFileMetadata
    )
    {
        parent::__construct(
            $type,
            $runtime,
            $errorFactory,
            $cacheState,
            $memcacheState,
            $cacheEngineFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $sourceFileMetadata
        );
    }

    public function getConfig($key = null, $default = null): mixed
    {
        return new ServiceCacheConfigRowDouble([
            'ENGINE' => 'file',
            'LIFETIME' => 10,
        ]);
    }

    protected function _getEngine($name, $object = true): mixed
    {
        return $object ? null : '\\' . ServiceCacheEngineDouble::class;
    }
}

final class ServiceCacheProbe extends cache
{
    /**
     * @var array<string, ServiceCacheEngineDouble>
     */
    private array $engines = [];

    public function __construct(?object $sourceFileMetadata = null)
    {
        $property = new ReflectionProperty(cache::class, 'sourceFileMetadata');
        $property->setValue($this, $sourceFileMetadata ?? new ServiceCacheSourceFileMetadataDouble());
    }

    public function getEngine(string $key): CacheEngineBase
    {
        if (!isset($this->engines[$key])) {
            $this->engines[$key] = new ServiceCacheEngineDouble($this, 'test', $key, ['LIFETIME' => 10]);
        }

        return $this->engines[$key];
    }

    public function engine(string $key): ServiceCacheEngineDouble
    {
        return $this->getEngine($key);
    }
}

final class ServiceCacheSourceFileMetadataDouble
{
    public array $isFileCalls = [];
    public array $sizeCalls = [];
    public array $modifiedTimeCalls = [];

    public function __construct(
        private bool $isFile = true,
        private int|false $size = 0,
        private int|false $modifiedTime = 0
    ) {
    }

    public function isFile(string $path): bool
    {
        $this->isFileCalls[] = $path;

        return $this->isFile;
    }

    public function size(string $path): int|false
    {
        $this->sizeCalls[] = $path;

        return $this->size;
    }

    public function modifiedTime(string $path): int|false
    {
        $this->modifiedTimeCalls[] = $path;

        return $this->modifiedTime;
    }
}

final class ServiceCacheEngineDouble extends CacheEngineBase
{
    protected function _loadData(bool $loadMetaOnly): bool
    {
        return false;
    }

    protected function _saveData(): static
    {
        return $this;
    }

    public function extraPath(): ?string
    {
        $property = new ReflectionProperty(CacheEngineBase::class, 'extraPath');
        return $property->getValue($this);
    }
}

final class ServiceCacheRuntimeDouble
{
    public int $calls = 0;
    public ServiceCacheInitializerDouble $initializer;

    public function __construct(private array $configCache)
    {
        $this->initializer = new ServiceCacheInitializerDouble();
    }

    public function getConfigCache(): array
    {
        $this->calls++;

        return $this->configCache;
    }

    public function getInitializer(): ServiceCacheInitializerDouble
    {
        return $this->initializer;
    }

    public function classNameResolver(): callable
    {
        return static fn(object|string $object): string => \fan\core\base\get_class_name($object) ?? (is_object($object) ? get_class($object) : $object);
    }
}

final class ServiceCacheInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceCacheConfiguratorDouble
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

final class ServiceCacheConfigDouble
{
    public function __construct(private array $data)
    {
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->data;
        }

        return $this->data[$key] ?? $default;
    }
}

final class ServiceCacheConfigRowDouble
{
    public function __construct(private array $data)
    {
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
}

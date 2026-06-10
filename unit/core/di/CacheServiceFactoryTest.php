<?php

declare(strict_types=1);

use fan\core\di\cache_service_factory;
use fan\core\service\cache;
use fan\core\service\cache_memcache_state;
use fan\core\service\cache_state;
use PHPUnit\Framework\TestCase;

final class CacheServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreCacheServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $runtime = new CacheServiceFactoryRuntimeDouble();
        $errorFactory = static fn(): object => new stdClass();
        $cacheState = new cache_state();
        $memcacheState = new cache_memcache_state();
        $cacheEngineFactoryCalls = [];
        $cacheEngineFactory = static function () use (&$cacheEngineFactoryCalls): object {
            $cacheEngineFactoryCalls[] = func_get_args();

            return new stdClass();
        };
        $serviceConfigurator = new CacheServiceFactoryConfiguratorDouble(new CacheServiceFactoryConfigDouble([
            'ENABLED' => true,
        ]));
        $serviceCacheFactoryCalls = [];
        $serviceCacheFactory = static function (string $type) use (&$serviceCacheFactoryCalls): object {
            $serviceCacheFactoryCalls[] = $type;

            return (object)['type' => $type];
        };
        $sourceFileMetadata = new stdClass();
        $configCacheFatalExceptionFactory = static fn(): Throwable => new RuntimeException('config fatal');
        $overrideCalls = [];

        $cache = (new cache_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            cache::class,
            'common_by_file',
            $runtime,
            $errorFactory,
            $cacheState,
            $memcacheState,
            $cacheEngineFactory,
            $runtime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $sourceFileMetadata,
            $configCacheFatalExceptionFactory
        );

        $this->assertInstanceOf(cache::class, $cache);
        $this->assertSame($cache, $cacheState->getInstance('common_by_file'));
        $this->assertSame([], $overrideCalls);
        $this->assertSame([cache::class], $runtime->initializer->serviceParams);
        $this->assertSame([$cache], $serviceConfigurator->getServiceConfigCalls);
        $this->assertSame([
            ['cache', 'ENABLED'],
        ], $serviceConfigurator->resetCalls);
        $this->assertSame([], $cacheEngineFactoryCalls);
        $this->assertSame([], $serviceCacheFactoryCalls);
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $runtime = new stdClass();
        $errorFactory = static fn(): object => new stdClass();
        $cacheState = new stdClass();
        $memcacheState = new stdClass();
        $cacheEngineFactory = static fn(): object => new stdClass();
        $serviceConfigurator = new stdClass();
        $serviceCacheFactory = static fn(string $type): object => (object)['type' => $type];
        $sourceFileMetadata = new stdClass();
        $configCacheFatalExceptionFactory = static fn(): Throwable => new RuntimeException('config fatal');
        $configuredCalls = [];

        $cache = (new cache_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            CacheServiceFactoryServiceDouble::class,
            'common_by_file',
            $runtime,
            $errorFactory,
            $cacheState,
            $memcacheState,
            $cacheEngineFactory,
            $runtime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $sourceFileMetadata,
            $configCacheFatalExceptionFactory
        );

        $this->assertInstanceOf(CacheServiceFactoryServiceDouble::class, $cache);
        $this->assertSame('common_by_file', $cache->type);
        $this->assertSame(
            [$runtime, $errorFactory, $cacheState, $memcacheState, $cacheEngineFactory, $runtime, $serviceConfigurator, $serviceCacheFactory, $sourceFileMetadata, $configCacheFatalExceptionFactory],
            $cache->dependencies
        );
        $this->assertSame(CacheServiceFactoryServiceDouble::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([
            'common_by_file',
            $runtime,
            $errorFactory,
            $cacheState,
            $memcacheState,
            $cacheEngineFactory,
            $runtime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $sourceFileMetadata,
            $configCacheFatalExceptionFactory,
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

final class CacheServiceFactoryServiceDouble
{
    public array $dependencies;

    public function __construct(
        public string $type,
        object $runtime,
        callable $errorFactory,
        object $cacheState,
        object $memcacheState,
        callable $cacheEngineFactory,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?object $sourceFileMetadata = null,
        ?callable $configCacheFatalExceptionFactory = null
    ) {
        $this->dependencies = [
            $runtime,
            $errorFactory,
            $cacheState,
            $memcacheState,
            $cacheEngineFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $sourceFileMetadata,
            $configCacheFatalExceptionFactory,
        ];
    }
}


final class CacheServiceFactoryRuntimeDouble
{
    public CacheServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new CacheServiceFactoryInitializerDouble();
    }

    public function getInitializer(): CacheServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    public function classNameResolver(): callable
    {
        return static fn(object|string $object): string => get_class_name($object) ?? (is_object($object) ? get_class($object) : $object);
    }
}

final class CacheServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class CacheServiceFactoryConfiguratorDouble
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

final class CacheServiceFactoryConfigDouble
{
    public function __construct(private array $values)
    {
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this : ($this->values[$key] ?? $default);
    }
}

<?php

declare(strict_types=1);

use fan\core\di\cache_engine_factory;
use fan\core\service\cache;
use fan\core\service\cache\base;
use fan\core\service\cache\file;
use fan\core\service\cache\memcache;
use PHPUnit\Framework\TestCase;

final class CacheEngineFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreFileCacheEngineWithTypedConstructor(): void
    {
        $serializerOperations = new CacheEngineFactorySerializerOperationsDouble();
        $facade = new CacheEngineFactoryFacadeDouble();
        $configuredFactoryCalled = false;

        $factory = new cache_engine_factory(
            $serializerOperations,
            static function () use (&$configuredFactoryCalled): object {
                $configuredFactoryCalled = true;
                throw new RuntimeException('Configured cache engine factory should not be called for core file engine.');
            },
            new stdClass(),
            null,
            null
        );

        $engine = $factory(
            file::class,
            $facade,
            'page',
            'home',
            ['ENGINE' => 'file'],
            new stdClass(),
            new stdClass(),
            null
        );

        $this->assertInstanceOf(file::class, $engine);
        $this->assertFalse($configuredFactoryCalled);
    }

    public function testFactoryCreatesCoreMemcacheCacheEngineWithTypedConstructor(): void
    {
        $serializerOperations = new CacheEngineFactorySerializerOperationsDouble();
        $memcacheState = new stdClass();
        $configFatalExceptionFactory = static fn(): Throwable => new RuntimeException('config fatal');
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $memcacheKeeperFactory = static fn(): object => new stdClass();
        $memcacheAvailabilityChecker = static fn(): bool => true;

        $engine = (new cache_engine_factory(
            $serializerOperations,
            static function (): object {
                throw new RuntimeException('Configured cache engine factory should not be called for core memcache engine.');
            },
            new stdClass(),
            $memcacheKeeperFactory,
            $memcacheAvailabilityChecker
        ))(
            memcache::class,
            new CacheEngineFactoryFacadeDouble(),
            'page',
            'home',
            ['ENGINE' => 'memcache'],
            new stdClass(),
            new stdClass(),
            $memcacheState,
            $configFatalExceptionFactory,
            $arrayValueReader
        );

        $this->assertInstanceOf(memcache::class, $engine);
        $property = new ReflectionProperty(memcache::class, 'configFatalExceptionFactory');
        $this->assertSame($configFatalExceptionFactory, $property->getValue($engine));
        $property = new ReflectionProperty(memcache::class, 'arrayValueReader');
        $this->assertSame($arrayValueReader, $property->getValue($engine));
        $property = new ReflectionProperty(memcache::class, 'memcacheKeeperFactory');
        $this->assertSame($memcacheKeeperFactory, $property->getValue($engine));
        $property = new ReflectionProperty(memcache::class, 'memcacheAvailabilityChecker');
        $this->assertSame($memcacheAvailabilityChecker, $property->getValue($engine));
    }

    public function testFactoryDelegatesConfiguredEngineWithSerializerDependencies(): void
    {
        $serializerOperations = new CacheEngineFactorySerializerOperationsDouble();
        $facade = new CacheEngineFactoryFacadeDouble();
        $logger = new stdClass();
        $runtime = new stdClass();
        $delegatedClass = null;
        $delegatedArguments = null;

        $factory = new cache_engine_factory(
            $serializerOperations,
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            },
            new stdClass(),
            null,
            null
        );

        $engine = $factory(
            CacheEngineFactoryEngineDouble::class,
            $facade,
            'page',
            'home',
            ['ENGINE' => 'file'],
            $logger,
            $runtime,
            null
        );

        $this->assertSame(CacheEngineFactoryEngineDouble::class, $delegatedClass);
        $this->assertIsArray($delegatedArguments);
        $this->assertInstanceOf(CacheEngineFactoryEngineDouble::class, $engine);
        $this->assertSame($facade, $delegatedArguments[0]);
        $this->assertSame('page', $delegatedArguments[1]);
        $this->assertSame('home', $delegatedArguments[2]);
        $this->assertSame(['ENGINE' => 'file'], $delegatedArguments[3]);
        $this->assertSame($logger, $delegatedArguments[4]);
        $this->assertSame($runtime, $delegatedArguments[5]);
        $this->assertSame('encoded:payload', $engine->exposeEncodePayload('payload'));
        $this->assertSame(['decoded' => 'payload'], $engine->exposeDecodePayload('payload', 'Cache payload'));
        $this->assertTrue($engine->exposeIsJsonPayload('json'));
    }

    public function testFactoryPassesMemcacheStateOnlyToConfiguredMemcacheEngine(): void
    {
        $serializerOperations = new CacheEngineFactorySerializerOperationsDouble();
        $memcacheState = new stdClass();
        $delegatedArguments = null;
        $factory = new cache_engine_factory(
            $serializerOperations,
            static function (string $className, array $arguments) use (&$delegatedArguments): object {
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            },
            new stdClass(),
            null,
            null
        );

        $engine = $factory(
            CacheEngineFactoryMemcacheEngineDouble::class,
            new CacheEngineFactoryFacadeDouble(),
            'page',
            'home',
            ['ENGINE' => 'memcache'],
            new stdClass(),
            new stdClass(),
            $memcacheState
        );

        $this->assertIsArray($delegatedArguments);
        $this->assertInstanceOf(CacheEngineFactoryMemcacheEngineDouble::class, $engine);
        $this->assertSame($memcacheState, $delegatedArguments[6]);
        $this->assertSame($memcacheState, $engine->keeperStateArg);
        $this->assertSame('encoded:payload', $engine->exposeEncodePayload('payload'));
    }

                private static function invokeFactoryWithClass(string $className, cache_engine_factory $factory): void
    {
        $factory(
            $className,
            new CacheEngineFactoryFacadeDouble(),
            'page',
            'home',
            ['ENGINE' => 'file'],
            new stdClass(),
            new stdClass(),
            null
        );
    }
}

final class CacheEngineFactoryFacadeDouble extends cache
{
    public function __construct()
    {
    }
}

final class CacheEngineFactoryEngineDouble extends base
{
    public array $constructorArgs = [];

    public function __construct(
        cache $facade,
        string $type,
        string $key,
        array $config,
        ?object $errorLogger = null,
        ?object $runtime = null,
        ?callable $payloadEncoder = null,
        ?callable $payloadDecoder = null,
        ?callable $jsonPayloadChecker = null
    ) {
        $this->constructorArgs = func_get_args();
        parent::__construct(
            $facade,
            $type,
            $key,
            $config,
            $errorLogger,
            $runtime,
            $payloadEncoder,
            $payloadDecoder,
            $jsonPayloadChecker
        );
    }

    protected function _loadData(bool $loadMetaOnly): bool
    {
        return false;
    }

    protected function _saveData(): static
    {
        return $this;
    }

    public function exposeEncodePayload(mixed $value): string
    {
        return $this->_encodePayload($value);
    }

    public function exposeDecodePayload(string $payload, string $errorTitle): mixed
    {
        return $this->_decodePayload($payload, $errorTitle);
    }

    public function exposeIsJsonPayload(string $payload): bool
    {
        return $this->_isJsonPayload($payload);
    }
}

final class CacheEngineFactoryMemcacheEngineDouble extends memcache
{
    public ?object $keeperStateArg = null;

    public function __construct(
        cache $facade,
        string $type,
        string $key,
        array $config,
        ?object $errorLogger = null,
        ?object $runtime = null,
        ?object $keeperState = null,
        ?callable $payloadEncoder = null,
        ?callable $payloadDecoder = null,
        ?callable $jsonPayloadChecker = null,
        ?callable $arrayValueReader = null
    ) {
        $this->keeperStateArg = $keeperState;
        parent::__construct(
            $facade,
            $type,
            $key,
            $config,
            $errorLogger,
            $runtime,
            $keeperState,
            $payloadEncoder,
            $payloadDecoder,
            $jsonPayloadChecker,
            null,
            $arrayValueReader
        );
    }

    public function exposeEncodePayload(mixed $value): string
    {
        return $this->_encodePayload($value);
    }
}


final class CacheEngineFactorySerializerOperationsDouble
{
    public function jsonPayloadEncoder(): callable
    {
        return static fn(mixed $value): string => 'encoded:' . (string)$value;
    }

    public function externalPayloadDecoder(): callable
    {
        return static fn(string $payload): array => ['decoded' => $payload];
    }

    public function jsonPayloadChecker(): callable
    {
        return static fn(string $payload): bool => $payload === 'json';
    }
}

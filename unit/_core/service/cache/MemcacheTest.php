<?php

declare(strict_types=1);

use fan\core\service\cache;
use fan\core\service\cache\memcache;
use fan\core\service\cache_memcache_state;
use FanTest\_core\SourceFileContractTestCase;

class ServiceCacheMemcacheTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/cache/memcache.php';

    public function testGetLoadsActualMetaAndPayloadFromKeeper(): void
    {
        $keeper = new ServiceCacheMemcacheKeeperDouble([
            'page-home-meta' => [
                'data_type' => 'string',
                'create_date' => date('Y-m-d H:i:s'),
                'lifetime' => 0,
            ],
            'page-home-data' => 'cached html',
        ]);
        $engine = $this->engine($keeper);

        $this->assertSame('cached html', $engine->get('fallback'));
        $this->assertTrue($engine->isLoaded());
        $this->assertSame([
            'page-home-meta',
            'page-home-data',
        ], $keeper->getCalls);
    }

    public function testMetaOnlyLoadKeepsEngineUnloadedButReturnsMeta(): void
    {
        $meta = [
            'data_type' => 'array',
            'create_date' => date('Y-m-d H:i:s'),
            'lifetime' => 0,
        ];
        $engine = $this->engine(new ServiceCacheMemcacheKeeperDouble([
            'page-home-meta' => $meta,
            'page-home-data' => ['items' => [1]],
        ]));

        $this->assertSame($meta, $engine->getMeta(true));
        $this->assertFalse($engine->isLoaded());
    }

    public function testSetAutoSaveWritesMetaAndPayloadWithLifetime(): void
    {
        $keeper = new ServiceCacheMemcacheKeeperDouble();
        $engine = $this->engine($keeper, ['LIFETIME' => 45]);

        $engine->set(['items' => [1, 2]], true);

        $this->assertCount(2, $keeper->setCalls);
        $this->assertSame('page-home-meta', $keeper->setCalls[0]['key']);
        $this->assertSame(45, $keeper->setCalls[0]['expire']);
        $this->assertSame('array', $keeper->setCalls[0]['value']['data_type']);
        $this->assertSame('page-home-data', $keeper->setCalls[1]['key']);
        $this->assertSame(['items' => [1, 2]], $keeper->setCalls[1]['value']);
        $this->assertTrue($engine->isSaved());
    }

    public function testDeleteRemovesMetaAndPayloadKeysAndClearsState(): void
    {
        $keeper = new ServiceCacheMemcacheKeeperDouble();
        $engine = $this->engine($keeper);
        $engine->set('payload', false);

        $this->assertSame($engine, $engine->delete());

        $this->assertSame(['page-home-meta', 'page-home-data'], $keeper->deleteCalls);
        $this->assertSame('fallback', $engine->get('fallback'));
        $this->assertSame([], $engine->getMeta(false));
    }

    public function testMemcacheEngineNoLongerKeepsKeepersInStaticProperty(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('private ?object $keeperState = null;', $source);
        $this->assertStringContainsString('$this->createCacheFatalException(', $source);
        $this->assertStringContainsString('$this->createConfigFatalException(', $source);
        $this->assertStringNotContainsString('private static array $keepers', $source);
        $this->assertStringNotContainsString('self::$keepers', $source);
        $this->assertStringContainsString('private mixed $arrayValueReader = null;', $source);
        $this->assertStringContainsString('$arrayValueReader = $this->arrayValueReader();', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringContainsString('private mixed $memcacheKeeperFactory = null,', $source);
        $this->assertStringContainsString('private mixed $memcacheAvailabilityChecker = null', $source);
        $this->assertStringContainsString('$keeper = ($this->memcacheKeeperFactory())();', $source);
        $this->assertStringNotContainsString('new \Memcache', $source);
        $this->assertStringNotContainsString("class_exists('\\Memcache')", $source);
        $this->assertStringNotContainsString('new \\fan\\core\\exception\\fatal', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
    }

    public function testConfigLookupUsesInjectedArrayValueReader(): void
    {
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $engine = new ServiceCacheMemcacheProbe(
            new ServiceCacheMemcacheFacadeDouble(),
            'page',
            'home',
            ['LIFETIME' => 30],
            keeperState: new cache_memcache_state(),
            arrayValueReader: $arrayValueReader
        );
        $method = new ReflectionMethod(memcache::class, 'arrayValueReader');

        $this->assertSame($arrayValueReader, $method->invoke($engine));
    }

    public function testInjectedKeeperFactoryCreatesAndCachesMemcacheKeeper(): void
    {
        $state = new cache_memcache_state();
        $keeper = new ServiceCacheMemcacheKeeperDouble();
        $factoryCalls = 0;
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $engine = new ServiceCacheMemcacheProbe(
            new ServiceCacheMemcacheFacadeDouble(),
            'page',
            'home',
            [
                'LIFETIME' => 30,
                'HOST' => '127.0.0.1',
                'PORT' => 11212,
            ],
            keeperState: $state,
            arrayValueReader: $arrayValueReader,
            memcacheKeeperFactory: static function () use (&$factoryCalls, $keeper): object {
                $factoryCalls++;

                return $keeper;
            },
            memcacheAvailabilityChecker: static fn(): bool => true
        );

        $this->assertSame($keeper, $engine->exposedKeeper());
        $this->assertSame($keeper, $state->getKeeper('page'));
        $this->assertSame(1, $factoryCalls);
        $this->assertSame([
            ['127.0.0.1', 11212],
        ], $keeper->addServerCalls);
    }

    public function testConfigCacheMemcacheFailureUsesInjectedCoreFatalFactory(): void
    {
        $expected = new RuntimeException('memcache missing');
        $calls = [];
        $engine = new ServiceCacheMemcacheProbe(
            new ServiceCacheMemcacheFacadeDouble(),
            'config',
            'service',
            ['LIFETIME' => 30],
            keeperState: new cache_memcache_state(),
            configFatalExceptionFactory: static function (
                string $message,
                int $code,
                ?Throwable $previous = null
            ) use (&$calls, $expected): Throwable {
                $calls[] = [$message, $code, $previous];

                return $expected;
            },
            memcacheAvailabilityChecker: static fn(): bool => false
        );

        try {
            $engine->exposedKeeper();
            $this->fail('Missing Memcache extension must throw the injected config fatal exception.');
        } catch (Throwable $exception) {
            $this->assertSame($expected, $exception);
        }

        $this->assertSame([
            ['Memcache doesn\'t setup there.', E_USER_ERROR, null],
        ], $calls);
    }

    private function engine(ServiceCacheMemcacheKeeperDouble $keeper, array $config = []): memcache
    {
        $state = new cache_memcache_state();
        $state->setKeeper('page', $keeper);

        return new memcache(
            new ServiceCacheMemcacheFacadeDouble(),
            'page',
            'home',
            array_replace(['LIFETIME' => 30], $config),
            null,
            null,
            $state
        );
    }
}

final class ServiceCacheMemcacheKeeperDouble
{
    public array $getCalls = [];
    public array $setCalls = [];
    public array $deleteCalls = [];
    public array $addServerCalls = [];

    public function __construct(private array $data = [])
    {
    }

    public function get(string $key): mixed
    {
        $this->getCalls[] = $key;

        return $this->data[$key] ?? false;
    }

    public function set(string $key, mixed $value, int $flag, int $expire): void
    {
        $this->setCalls[] = [
            'key' => $key,
            'value' => $value,
            'flag' => $flag,
            'expire' => $expire,
        ];
        $this->data[$key] = $value;
    }

    public function delete(string $key): void
    {
        $this->deleteCalls[] = $key;
        unset($this->data[$key]);
    }

    public function addServer(string $host, int $port): void
    {
        $this->addServerCalls[] = [$host, $port];
    }
}

final class ServiceCacheMemcacheFacadeDouble extends cache
{
    public function __construct()
    {
    }
}

final class ServiceCacheMemcacheProbe extends memcache
{
    public function exposedKeeper(): object
    {
        return $this->_getKeeper();
    }
}

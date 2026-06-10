<?php

declare(strict_types=1);

use fan\core\di\application_infrastructure_service_creator;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;
use fan\core\service\config\row;
use fan\project\service\cache;
use fan\project\service\config;
use fan\project\service\file_system;
use fan\project\service\json;


final class ApplicationInfrastructureServiceCreatorTest extends TestCase
{    public function testDefaultConfigCreatorFailsWhenConfigRowFactoryFactoryIsNotInjected(): void
    {
        $creator = new application_infrastructure_service_creator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config row factory factory is not configured for infrastructure service creator.');

        $creator->createConfigService(
            $this->containerWithInfrastructureDependencies(),
            new ApplicationInfrastructureStateDouble(),
            static fn(): object => (object)['service' => 'config']
        );
    }

    public function testConfigCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = $this->containerWithInfrastructureDependencies();
        $configState = new ApplicationInfrastructureStateDouble();
        $received = [];

        $rowFactory = static fn(mixed $data): row => new row($data);
        $rowFactoryFactoryCalls = [];
        $creator = new application_infrastructure_service_creator(
            static function (object $serializerOperations, callable $serviceExceptionFactory, callable $shortClassNameResolver) use (&$rowFactoryFactoryCalls, $rowFactory): callable {
                $rowFactoryFactoryCalls[] = [$serializerOperations, $serviceExceptionFactory, $shortClassNameResolver];

                return $rowFactory;
            }
        );

        $service = $creator->createConfigService(
            $container,
            $configState,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'config'];
            },
            'service',
            'arr'
        );

        $this->assertSame('config', $service->service);
        $this->assertSame('\\' . config::class, $received[0] ?? null);
        $this->assertSame('service', $received[1] ?? null);
        $this->assertSame('arr', $received[2] ?? null);
        $this->assertSame($configState, $received[5] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[6] ?? null);
        $this->assertSame($container->get('php_array_file_loader'), $received[10] ?? null);
        $this->assertSame($rowFactory, $received[11] ?? null);
        $this->assertSame($container->get('cache_source_file_metadata'), $received[12] ?? null);
        $this->assertSame($container->get('config_source_file_storage'), $received[13] ?? null);
        $this->assertSame($container->get('short_class_name_resolver'), $received[14] ?? null);
        $this->assertSame($container->get('serializer_operations'), $rowFactoryFactoryCalls[0][0] ?? null);
        $this->assertSame($container->get('bootstrap_runtime')->serviceExceptionFactory(), $rowFactoryFactoryCalls[0][1] ?? null);
        $this->assertSame($container->get('short_class_name_resolver'), $rowFactoryFactoryCalls[0][2] ?? null);
    }

    public function testCacheJsonAndFileSystemCreatorsPassExplicitDependencies(): void
    {
        $container = $this->containerWithInfrastructureDependencies();
        $creator = new application_infrastructure_service_creator();
        $cacheState = new ApplicationInfrastructureStateDouble();
        $memcacheState = new stdClass();
        $cacheEngineFactory = static fn(): object => (object)['name' => 'cache_engine_factory'];
        $cacheCalls = [];
        $cacheServiceFactory = static function (mixed ...$arguments) use (&$cacheCalls): object {
            $cacheCalls[] = $arguments;

            return new ApplicationInfrastructureCacheDouble();
        };

        $configCache = $creator->createConfigCache($container, $cacheState, $memcacheState, $cacheEngineFactory, $cacheServiceFactory);
        $this->assertInstanceOf(ApplicationInfrastructureCacheDouble::class, $configCache);
        $this->assertSame('\\' . cache::class, $cacheCalls[0][0] ?? null);
        $this->assertSame(cache::CONFIG_TYPE, $cacheCalls[0][1] ?? null);
        $this->assertSame($container->get('cache_source_file_metadata'), $cacheCalls[0][10] ?? null);
        $this->assertIsCallable($cacheCalls[0][11] ?? null);

        $cache = $creator->createCacheService($container, $cacheState, $memcacheState, $cacheEngineFactory, $cacheServiceFactory, 'runtime');
        $this->assertInstanceOf(ApplicationInfrastructureCacheDouble::class, $cache);
        $this->assertSame('runtime', $cacheCalls[1][1] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $cacheCalls[1][2] ?? null);
        $this->assertSame($container->get('config'), $cacheCalls[1][8] ?? null);
        $this->assertIsCallable($cacheCalls[1][11] ?? null);

        $jsonState = new ApplicationInfrastructureStateDouble();
        $jsonCalls = [];
        $json = $creator->createJsonService(
            $container,
            $jsonState,
            static function (mixed ...$arguments) use (&$jsonCalls): object {
                $jsonCalls[] = $arguments;

                return (object)['service' => 'json'];
            },
            true
        );
        $this->assertSame('json', $json->service);
        $this->assertSame('\\' . json::class, $jsonCalls[0][0] ?? null);
        $this->assertTrue($jsonCalls[0][1] ?? null);
        $this->assertSame($container->get('config'), $jsonCalls[0][4] ?? null);

        $fileSystemState = new ApplicationInfrastructureStateDouble();
        $fileSystemCalls = [];
        $fileSystem = $creator->createFileSystemService(
            $container,
            $fileSystemState,
            static function (mixed ...$arguments) use (&$fileSystemCalls): object {
                $fileSystemCalls[] = $arguments;

                return (object)['service' => 'file_system'];
            },
            '{PROJECT}/tmp'
        );
        $this->assertSame('file_system', $fileSystem->service);
        $this->assertSame('\\' . file_system::class, $fileSystemCalls[0][0] ?? null);
        $this->assertSame('/parsed/{PROJECT}/tmp', $fileSystemCalls[0][1] ?? null);
        $this->assertSame($container->get('file_system_storage'), $fileSystemCalls[0][2] ?? null);
    }

    public function testCacheCreatorUsesInjectedServiceExceptionFactoryWhenDefaultTypeIsMissing(): void
    {
        $calls = [];
        $config = new ApplicationInfrastructureConfigDouble('');
        $runtime = new ApplicationInfrastructureRuntimeDouble(
            static function (
                string $exceptionClass,
                object $service,
                string $message,
                int $code,
                ?Throwable $previous
            ) use (&$calls): Throwable {
                $calls[] = [$exceptionClass, $service, $message, $code, $previous];

                return new RuntimeException('factory: ' . $message, $code, $previous);
            }
        );
        $container = $this->containerWithInfrastructureDependencies($config, $runtime);
        $creator = new application_infrastructure_service_creator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('factory: Default CACHE-type doesn\'t set in config-file.');

        try {
            $creator->createCacheService(
                $container,
                new ApplicationInfrastructureStateDouble(),
                new stdClass(),
                static fn(): object => new stdClass(),
                static fn(): object => new ApplicationInfrastructureCacheDouble()
            );
        } finally {
            $this->assertCount(1, $calls);
            $this->assertSame('\fan\project\exception\service\fatal', $calls[0][0]);
            $this->assertSame($config, $calls[0][1]);
            $this->assertSame('Default CACHE-type doesn\'t set in config-file.', $calls[0][2]);
            $this->assertSame(E_USER_ERROR, $calls[0][3]);
            $this->assertNull($calls[0][4]);
        }
    }

    public function testConfigCacheUsesInjectedError500FactoryWhenCacheInstancesAlreadyExist(): void
    {
        $calls = [];
        $runtime = new ApplicationInfrastructureRuntimeDouble();
        $container = $this->containerWithInfrastructureDependencies(
            runtime: $runtime,
            error500ExceptionFactory: static function (string $message, int $code, ?Throwable $previous = null) use (&$calls): Throwable {
                $calls[] = [$message, $code, $previous];

                return new RuntimeException('factory: ' . $message, $code, $previous);
            }
        );
        $cacheState = new ApplicationInfrastructureStateDouble();
        $cacheState->setInstance('runtime', new stdClass());
        $creator = new application_infrastructure_service_creator();

        $result = $creator->createConfigCache(
            $container,
            $cacheState,
            new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new ApplicationInfrastructureCacheDouble()
        );

        $this->assertNull($result);
        $this->assertSame([
            ['It\'s inpossible to get config-Instance after make another Instances.', E_USER_ERROR, null],
        ], $calls);
        $this->assertSame([
            'factory: It\'s inpossible to get config-Instance after make another Instances.',
        ], $runtime->loggedErrors);
    }

    public function testCacheCreatorUsesInjectedError500FactoryForConfigCacheType(): void
    {
        $calls = [];
        $container = $this->containerWithInfrastructureDependencies(
            error500ExceptionFactory: static function (string $message, int $code, ?Throwable $previous = null) use (&$calls): Throwable {
                $calls[] = [$message, $code, $previous];

                return new RuntimeException('factory: ' . $message, $code, $previous);
            }
        );
        $creator = new application_infrastructure_service_creator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('factory: It\'s inpossible to get config-Instance by usual way.');

        try {
            $creator->createCacheService(
                $container,
                new ApplicationInfrastructureStateDouble(),
                new stdClass(),
                static fn(): object => new stdClass(),
                static fn(): object => new ApplicationInfrastructureCacheDouble(),
                cache::CONFIG_TYPE
            );
        } finally {
            $this->assertSame([
                ['It\'s inpossible to get config-Instance by usual way.', E_USER_ERROR, null],
            ], $calls);
        }
    }

    private function containerWithInfrastructureDependencies(
        ?object $config = null,
        ?object $runtime = null,
        ?callable $error500ExceptionFactory = null
    ): container
    {
        $container = new container();
        $config ??= new ApplicationInfrastructureConfigDouble();
        $runtime ??= new ApplicationInfrastructureRuntimeDouble();
        $container
            ->factory('bootstrap_runtime', static fn(): object => $runtime)
            ->factory('config', static fn(): object => $config)
            ->factory('config_cache', static fn(): object => (object)['name' => 'config_cache'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('error', static fn(): object => (object)['name' => 'error'])
            ->factory('request_input', static fn(): object => (object)['name' => 'request-input'])
            ->factory('header_writer', static fn(): object => (object)['name' => 'header-writer'])
            ->factory('core_fatal_exception_factory', static fn(): callable => static fn(string $message, mixed ...$arguments): Throwable => new RuntimeException($message))
            ->factory(
                'error500_exception_factory',
                static fn(): callable => $error500ExceptionFactory
                    ?? static fn(string $message, int $code = E_USER_ERROR, ?Throwable $previous = null): Throwable => new RuntimeException($message, $code, $previous)
            )
            ->factory('php_array_file_loader', static fn(): callable => static fn(string $path, mixed $default = null): mixed => $default)
            ->factory('serializer_operations', static fn(): object => new ApplicationInfrastructureSerializerOperationsDouble())
            ->factory('short_class_name_resolver', static fn(): callable => static fn(object|string $object): string => is_object($object) ? get_class($object) : $object)
            ->factory('cache_source_file_metadata', static fn(): object => (object)['name' => 'cache_source_file_metadata'])
            ->factory('config_source_file_storage', static fn(): object => (object)['name' => 'config_source_file_storage'])
            ->factory('file_system_storage', static fn(): object => (object)['name' => 'file_system_storage']);

        return $container;
    }
}

final class ApplicationInfrastructureStateDouble
{
    private array $instances = [];

    public function getInstance(mixed $key): mixed
    {
        return $this->instances[$this->stateKey($key)] ?? null;
    }

    public function setInstance(mixed $key, mixed $instance): void
    {
        $this->instances[$this->stateKey($key)] = $instance;
    }

    public function hasInstances(): bool
    {
        return $this->instances !== [];
    }

    private function stateKey(mixed $key): string
    {
        return is_bool($key) ? ($key ? 'true' : 'false') : (string)$key;
    }
}

final class ApplicationInfrastructureRuntimeDouble
{
    public array $loggedErrors = [];

    private $serviceExceptionFactory;

    public function __construct(?callable $serviceExceptionFactory = null)
    {
        $this->serviceExceptionFactory = $serviceExceptionFactory ?? static fn(): \Throwable => new RuntimeException('service exception');
    }

    public function parsePath(string $path): string
    {
        return '/parsed/' . $path;
    }

    public function logError(string $message): void
    {
        $this->loggedErrors[] = $message;
    }

    public function serviceExceptionFactory(): callable
    {
        return $this->serviceExceptionFactory;
    }
}

final class ApplicationInfrastructureConfigDouble
{
    public function __construct(private string $defaultCacheType = 'runtime')
    {
    }

    public function get(string $section): object
    {
        return new class($this->defaultCacheType) {
            public function __construct(private string $defaultCacheType)
            {
            }

            public function get(string $key): string
            {
                return $this->defaultCacheType;
            }
        };
    }
}

final class ApplicationInfrastructureSerializerOperationsDouble
{
    public function phpSnapshotEncoder(): callable
    {
        return static fn(mixed $value): string => serialize($value);
    }

    public function phpSnapshotDecoder(): callable
    {
        return static fn(string $value): mixed => unserialize($value);
    }
}

final class ApplicationInfrastructureCacheDouble
{
    public function get(string $key): object
    {
        return (object)['key' => $key];
    }
}

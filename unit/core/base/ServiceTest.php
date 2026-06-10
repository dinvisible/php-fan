<?php

declare(strict_types=1);

use FanTest\core\SourceFileContractTestCase;
use fan\core\base\service;
use fan\core\base\service_dependencies;
use fan\core\service\service_listener_state;
use fan\project\exception\service\fatal;


class BaseServiceTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/base/service.php';

    public function testDelegateResolverKeepsMixedParameterContract(): void
    {
        $this->assertStringContainsString(
            'protected function _getDelegate(mixed $class): mixed',
            $this->sourceCode()
        );
    }

    public function testCheckNameMapsCoreNamespaceToProjectNamespace(): void
    {
        $this->assertSame(
            'fan\project\service\cache',
            BaseServiceProbe::checkName('fan\core\service\cache')
        );
        $this->assertSame(
            'vendor\package\service',
            BaseServiceProbe::checkName('vendor\package\service')
        );
    }

    public function testConfigAccessAndEnabledFlagUseConfigRowContract(): void
    {
        $config = new BaseServiceConfigDouble([
            'ENABLED' => false,
            'timeout' => 15,
            'nullable' => null,
        ]);
        $service = (new BaseServiceProbe())->setConfigDouble($config);

        $this->assertSame($config, $service->getConfig());
        $this->assertSame(15, $service->getConfig('timeout'));
        $this->assertSame(null, $service->getConfig('nullable', 'fallback'));
        $this->assertSame('fallback', $service->getConfig('missing', 'fallback'));
        $this->assertFalse($service->isEnabled());

        $this->assertTrue((new BaseServiceProbe())->setConfigDouble(new BaseServiceConfigDouble([]))->isEnabled());
        $this->assertFalse((new BaseServiceProbe())->isEnabled());
    }

    public function testExceptionPoliciesAcceptOnlySupportedValues(): void
    {
        $service = new BaseServiceProbe();

        $this->assertNull($service->getExceptionDbOper());
        $this->assertSame('service', $service->getExceptionLogType());
        $this->assertSame($service, $service->setExceptionDbOper('rollback'));
        $this->assertSame('rollback', $service->getExceptionDbOper());

        $service->setExceptionDbOper('invalid');
        $this->assertSame('rollback', $service->getExceptionDbOper());

        $service->setExceptionDbOper(null);
        $this->assertNull($service->getExceptionDbOper());

        $this->assertSame($service, $service->setExceptionLogType('php'));
        $this->assertSame('php', $service->getExceptionLogType());

        $service->setExceptionLogType('invalid');
        $this->assertSame('php', $service->getExceptionLogType());

        $service->setExceptionLogType(null);
        $this->assertSame('service', $service->getExceptionLogType());
    }

    public function testResetEnabledDelegatesToConfigurator(): void
    {
        $configurator = new BaseServiceConfiguratorDouble();
        $service = (new BaseServiceProbe())
            ->setServiceDependencies(serviceConfigurator: $configurator);

        $this->assertSame($service, $service->resetEnabled());
        $this->assertSame([
            ['BaseServiceProbe', 'ENABLED'],
        ], $configurator->resetCalls);
    }

    public function testSetConfigDelegatesToConfiguratorAndStoresReturnedConfig(): void
    {
        $config = new BaseServiceConfigDouble(['ENABLED' => true]);
        $configurator = new BaseServiceConfiguratorDouble($config);
        $service = (new BaseServiceProbe())
            ->setServiceDependencies(serviceConfigurator: $configurator);

        $this->assertSame($service, $service->exposedSetConfig());
        $this->assertSame($config, $service->getConfig());
        $this->assertSame([$service], $configurator->getServiceConfigCalls);
    }

    public function testBaseServiceRequiresExplicitConfigurator(): void
    {
        $service = new BaseServiceProbe();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config service is not configured for BaseServiceProbe.');

        $service->resetEnabled();
    }

    public function testConstructorCanUseInjectedRuntimeConfiguratorAndCacheFactory(): void
    {
        $runtime = new BaseServiceRuntimeDouble();
        $config = new BaseServiceConfigDouble(['ENABLED' => true]);
        $configurator = new BaseServiceConfiguratorDouble($config);
        $cache = new BaseServiceCacheDouble([
            'BaseServiceInitializedProbe' => [
                'existing' => 'stored',
            ],
        ]);
        $cacheFactoryCalls = [];

        $service = new BaseServiceInitializedProbe(
            true,
            $runtime,
            $configurator,
            static function (string $type) use ($cache, &$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return $cache;
            }
        );

        $this->assertSame([BaseServiceInitializedProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$service], $configurator->getServiceConfigCalls);
        $this->assertSame($config, $service->getConfig());
        $this->assertSame('stored', $service->exposedGetCacheData('existing'));
        $this->assertSame(['service_data'], $cacheFactoryCalls);
    }

    public function testConstructorAcceptsServiceDependenciesValueObject(): void
    {
        $runtime = new BaseServiceRuntimeDouble();
        $config = new BaseServiceConfigDouble(['ENABLED' => true]);
        $configurator = new BaseServiceConfiguratorDouble($config);
        $cache = new BaseServiceCacheDouble([
            'BaseServiceDependenciesInitializedProbe' => [
                'existing' => 'stored',
            ],
        ]);
        $cacheFactoryCalls = [];
        $dependencies = new service_dependencies(
            $runtime,
            $configurator,
            static function (string $type) use ($cache, &$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return $cache;
            }
        );

        $service = new BaseServiceDependenciesInitializedProbe(true, $dependencies);

        $this->assertSame([BaseServiceDependenciesInitializedProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$service], $configurator->getServiceConfigCalls);
        $this->assertSame($config, $service->getConfig());
        $this->assertSame('stored', $service->exposedGetCacheData('existing'));
        $this->assertSame(['service_data'], $cacheFactoryCalls);
    }

    public function testCacheHelpersReadAndWriteByServiceShortName(): void
    {
        $cache = new BaseServiceCacheDouble([
            'BaseServiceProbe' => [
                'existing' => 'stored',
            ],
        ]);
        $service = (new BaseServiceProbe())
            ->setServiceDependencies(serviceCacheFactory: static fn(string $type): object => $cache);

        $this->assertSame('stored', $service->exposedGetCacheData('existing'));
        $this->assertSame('fallback', $service->exposedGetCacheData('missing', 'fallback'));
        $this->assertSame($service, $service->exposedSetCacheData('new-key', 'new-value'));
        $this->assertSame([
            'existing' => 'stored',
            'new-key' => 'new-value',
        ], $cache->data['BaseServiceProbe']);
        $this->assertSame([
            ['BaseServiceProbe', []],
            ['BaseServiceProbe', []],
            ['BaseServiceProbe', []],
        ], $cache->getCalls);
        $this->assertSame([
            ['BaseServiceProbe', ['existing' => 'stored', 'new-key' => 'new-value']],
        ], $cache->setCalls);
    }

    public function testListenersAreScopedByServiceClassNameAndEvent(): void
    {
        $listenerState = new service_listener_state();
        $received = [];
        $service = (new BaseServiceProbe())
            ->setServiceDependencies(serviceListenerState: $listenerState);

        $this->assertSame($service, $service->addListener('saved', static function (array $data) use (&$received): void {
            $received[] = $data;
        }));

        $service->exposedBroadcastMessage('ignored', ['id' => 1]);
        $service->exposedBroadcastMessage('saved', ['id' => 2]);

        $this->assertSame([
            ['id' => 2],
        ], $received);
    }

    public function testSubscribeForServiceCanTargetAnotherServiceName(): void
    {
        $listenerState = new service_listener_state();
        $received = [];
        $service = (new BaseServiceProbe())
            ->setServiceDependencies(serviceListenerState: $listenerState);

        $service->exposedSubscribeForService('OtherService', 'saved', static function (array $data) use (&$received): void {
            $received[] = $data;
        });
        $service->exposedBroadcastMessage('saved', ['id' => 1]);

        $this->assertSame([], $received);

        $listeners = $listenerState->listeners();
        $this->assertArrayHasKey('OtherService', $listeners);
        $this->assertArrayHasKey('saved', $listeners['OtherService']);
    }

    public function testListenerStateRequiresExplicitDependency(): void
    {
        $service = new BaseServiceProbe();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Service listener state is not configured for BaseServiceProbe.');

        $service->addListener('saved', static fn(): null => null);
    }

    public function testRuntimeProvidesSharedListenerStateToServices(): void
    {
        $runtime = new BaseServiceRuntimeDouble();
        $received = [];
        $subscriber = (new BaseServiceProbe())
            ->setServiceDependencies(serviceBootstrapRuntime: $runtime);
        $publisher = (new BaseServiceProbe())
            ->setServiceDependencies(serviceBootstrapRuntime: $runtime);

        $subscriber->addListener('saved', static function (array $data) use (&$received): void {
            $received[] = $data;
        });
        $publisher->exposedBroadcastMessage('saved', ['id' => 7]);

        $this->assertSame([
            ['id' => 7],
        ], $received);
    }

    public function testMagicCallRoutesConfiguredDelegateMethodAndCachesDelegate(): void
    {
        $delegate = new BaseServiceDelegateDouble();
        $service = (new BaseServiceProbe())
            ->setDelegateRuleData(['worker' => ['doWork']])
            ->setDelegateData('worker', $delegate);

        $this->assertSame('processed:item:3', $service->doWork('item', 3));
        $this->assertSame([
            ['item', 3],
        ], $delegate->calls);
    }

    public function testEngineCreationUsesInjectedFactory(): void
    {
        $runtime = new BaseServiceRuntimeDouble(loadClassResult: true);
        $factoryCalls = [];
        $service = (new BaseServiceProbe())
            ->setServiceDependencies(
                serviceBootstrapRuntime: $runtime,
                serviceEngineFactory: static function (string $class) use (&$factoryCalls): object {
                    $factoryCalls[] = $class;

                    return new BaseServiceEngineDouble();
                }
            );

        $engine = $service->exposedGetEngine('delegate\worker');

        $this->assertInstanceOf(BaseServiceEngineDouble::class, $engine);
        $this->assertSame($service, $engine->facade);
        $this->assertSame([
            ['BaseServiceProbe\delegate\worker', true],
        ], $runtime->loadClassCalls);
        $this->assertSame(['\BaseServiceProbe\delegate\worker'], $factoryCalls);
    }

    public function testEngineClassLookupCanReturnClassNameWithoutFactory(): void
    {
        $runtime = new BaseServiceRuntimeDouble(loadClassResult: true);
        $service = (new BaseServiceProbe())
            ->setServiceDependencies(serviceBootstrapRuntime: $runtime);

        $this->assertSame('\BaseServiceProbe\delegate\worker', $service->exposedGetEngine('delegate\worker', false));
    }

    public function testMagicCallReturnsNullWhenExtensionCallHandlesMethod(): void
    {
        $service = new BaseServiceExtensionProbe(true);

        $this->assertNull($service->virtualMethod('alpha', 'beta'));
        $this->assertSame('virtualMethod', $service->extensionMethod);
        $this->assertSame(['alpha', 'beta'], $service->extensionArgs);
    }

    public function testMagicCallThrowsFatalExceptionForUnknownMethod(): void
    {
        $calls = [];
        $service = (new BaseServiceProbe())
            ->setExceptionLogType('nothing')
            ->setServiceDependencies(serviceExceptionFactory: $this->serviceExceptionFactory($calls));

        $this->expectException(fatal::class);
        $this->expectExceptionMessage('Incorrect call of service - unknown method "missingMethod"!');

        try {
            $service->missingMethod();
        } finally {
            $this->assertSame('\fan\project\exception\service\fatal', $calls[0][0] ?? null);
            $this->assertSame($service, $calls[0][1] ?? null);
            $this->assertSame('Incorrect call of service - unknown method "missingMethod"!', $calls[0][2] ?? null);
        }
    }

    public function testMakeServiceExceptionUsesDefaultDbOperationAndPreviousException(): void
    {
        $calls = [];
        $service = (new BaseServiceProbe())
            ->setExceptionLogType('nothing')
            ->setServiceDependencies(serviceExceptionFactory: $this->serviceExceptionFactory($calls));
        $previous = new \RuntimeException('previous failure');

        try {
            $service->exposedMakeServiceException('failure message', 'nothing', E_USER_WARNING, $previous);
            $this->fail('Expected service fatal exception was not thrown.');
        } catch (fatal $exception) {
            $this->assertSame($service, $exception->getService());
            $this->assertSame('failure message', $exception->getMessage());
            $this->assertSame(E_USER_WARNING, $exception->getCode());
            $this->assertSame($previous, $exception->getPrevious());
            $this->assertSame('nothing', $service->getExceptionDbOper());
            $this->assertNull($exception->getDbOper());
            $this->assertSame('\fan\project\exception\service\fatal', $calls[0][0]);
            $this->assertSame($service, $calls[0][1]);
            $this->assertSame('failure message', $calls[0][2]);
            $this->assertSame(E_USER_WARNING, $calls[0][3]);
            $this->assertSame($previous, $calls[0][4]);
        }
    }

    public function testSourceNoLongerReadsContainerRegistry(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('container_registry::get()', $source);
        $this->assertStringNotContainsString('resolveService(', $source);
        $this->assertStringNotContainsString('getServiceContainer(', $source);
        $this->assertStringNotContainsString('setServiceContainer(', $source);
        $this->assertStringNotContainsString('new $class();', $source);
        $this->assertStringNotContainsString('new \\fan\\core\\service\\service_listener_state()', $source);
        $this->assertStringNotContainsString('new \\fan\\core\\service\\service_single_state()', $source);
        $this->assertStringNotContainsString('new fatalException(', $source);
        $this->assertStringNotContainsString('new \\fan\\project\\exception\\service\\fatal(', $source);
        $this->assertStringNotContainsString('get_class_name(', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringContainsString('Service listener state is not configured for', $source);
        $this->assertStringContainsString('Service single state is not configured for', $source);
        $this->assertStringContainsString('private $serviceEngineFactory = null;', $source);
        $this->assertStringContainsString('private $serviceExceptionFactory = null;', $source);
        $this->assertStringContainsString('private $classNameResolver = null;', $source);
        $this->assertStringContainsString('private $arrayValueReader = null;', $source);
        $this->assertStringContainsString('protected function serviceEngineFactory(): callable', $source);
        $this->assertStringContainsString('protected function serviceClassName(): string', $source);
        $this->assertStringContainsString('protected function arrayValueReader(): callable', $source);
        $this->assertStringContainsString('protected function createServiceFatalException(string $message, int $code = E_USER_ERROR, ?\\Throwable $previous = null): \\Throwable', $source);
    }

    private function serviceExceptionFactory(array &$calls = []): callable
    {
        return static function (
            string $exceptionClass,
            service $service,
            string $message,
            int $code = E_USER_ERROR,
            ?\Throwable $previous = null
        ) use (&$calls): \Throwable {
            $calls[] = [$exceptionClass, $service, $message, $code, $previous];

            return new fatal($service, $message, $code, $previous);
        };
    }

}

class BaseServiceProbe extends service
{
    public function __construct()
    {
        $this->setServiceDependencies(
            classNameResolver: static function (string|object $object): string {
                if (is_object($object)) {
                    $object = get_class($object);
                }
                $parts = explode('\\', $object);

                return (string)end($parts);
            },
            arrayValueReader: static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => self::readArrayValue($array, $key, $default)
        );
    }

    private static function readArrayValue(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed
    {
        if ($key === null) {
            return $default;
        }
        if (is_array($key)) {
            if ($key === []) {
                return $default;
            }
            $firstKey = array_shift($key);
            if ($key !== []) {
                return isset($array[$firstKey]) && (is_array($array[$firstKey]) || $array[$firstKey] instanceof \ArrayAccess)
                    ? self::readArrayValue($array[$firstKey], $key, $default)
                    : $default;
            }
            $key = $firstKey;
        }

        return $array[$key] ?? $default;
    }

    public function isSingleton(): bool
    {
        return false;
    }

    public function setConfigDouble(?object $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function setDelegateRuleData(array $delegateRule): self
    {
        $this->delegateRule = $delegateRule;

        return $this;
    }

    public function setDelegateData(string $class, object $delegate): self
    {
        $this->delegate[$class] = $delegate;

        return $this;
    }

    public function exposedSetConfig(): self
    {
        return $this->_setConfig();
    }

    public function exposedGetCacheData(string $key, mixed $default = null): mixed
    {
        return $this->_getCacheData($key, $default);
    }

    public function exposedSetCacheData(string $key, mixed $value): self
    {
        return $this->_setCacheData($key, $value);
    }

    public function exposedSubscribeForService(string $serviceName, string $eventName, callable $callBack): void
    {
        $this->_subscribeForService($serviceName, $eventName, $callBack);
    }

    public function exposedBroadcastMessage(string $eventName, mixed $data): void
    {
        $this->_broadcastMessage($eventName, $data);
    }

    public function exposedGetEngine(mixed $name, bool $object = true): mixed
    {
        return $this->_getEngine($name, $object);
    }

    public function exposedMakeServiceException(
        string $logErrMsg,
        ?string $exceptionDbOper = 'rollback',
        int $code = E_USER_ERROR,
        ?\Exception $previous = null
    ): void {
        $this->_makeServiceException($logErrMsg, $exceptionDbOper, $code, $previous);
    }
}

final class BaseServiceExtensionProbe extends BaseServiceProbe
{
    public ?string $extensionMethod = null;
    public array $extensionArgs = [];

    public function __construct(private readonly bool $handled)
    {
    }

    protected function _extensionCall(string $method, array $args): bool
    {
        $this->extensionMethod = $method;
        $this->extensionArgs = $args;

        return $this->handled;
    }
}

final class BaseServiceInitializedProbe extends service
{
    public function __construct(
        bool $allowIni,
        object $runtime,
        object $configurator,
        callable $cacheFactory
    )
    {
        parent::__construct($allowIni, $runtime, $configurator, $cacheFactory);
    }

    public function isSingleton(): bool
    {
        return false;
    }

    public function exposedGetCacheData(string $key, mixed $default = null): mixed
    {
        return $this->_getCacheData($key, $default);
    }
}

final class BaseServiceDependenciesInitializedProbe extends service
{
    public function __construct(bool $allowIni, service_dependencies $dependencies)
    {
        parent::__construct($allowIni, $dependencies);
    }

    public function isSingleton(): bool
    {
        return false;
    }

    public function exposedGetCacheData(string $key, mixed $default = null): mixed
    {
        return $this->_getCacheData($key, $default);
    }
}

final class BaseServiceConfigDouble
{
    public function __construct(private readonly array $values)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->values) ? $this->values[$key] : $default;
    }
}

final class BaseServiceConfiguratorDouble
{
    public array $resetCalls = [];
    public array $getServiceConfigCalls = [];

    public function __construct(private readonly ?object $config = null)
    {
    }

    public function reset(string $serviceName, string $key): void
    {
        $this->resetCalls[] = [$serviceName, $key];
    }

    public function getServiceConfig(object $service): ?object
    {
        $this->getServiceConfigCalls[] = $service;

        return $this->config;
    }
}

final class BaseServiceCacheDouble
{
    public array $getCalls = [];
    public array $setCalls = [];

    public function __construct(public array $data = [])
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->getCalls[] = [$key, $default];

        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->setCalls[] = [$key, $value];
        $this->data[$key] = $value;
    }
}

final class BaseServiceRuntimeDouble
{
    public object $initializer;
    public array $loadClassCalls = [];
    private service_listener_state $listenerState;

    public function __construct(private readonly bool $loadClassResult = false)
    {
        $this->initializer = new class {
            public array $serviceParams = [];

            public function setServiceParam(string $class): void
            {
                $this->serviceParams[] = $class;
            }
        };
        $this->listenerState = new service_listener_state();
    }

    public function getInitializer(): object
    {
        return $this->initializer;
    }

    public function loadClass(string $class, bool $isException = false): bool
    {
        $this->loadClassCalls[] = [$class, $isException];

        return $this->loadClassResult;
    }

    public function serviceListenerState(): service_listener_state
    {
        return $this->listenerState;
    }

    public function serviceExceptionFactory(): callable
    {
        return static fn(
            string $exceptionClass,
            service $service,
            string $message,
            int $code = E_USER_ERROR,
            ?\Throwable $previous = null
        ): \Throwable => new fatal($service, $message, $code, $previous);
    }

    public function classNameResolver(): callable
    {
        return static function (string|object $object): string {
            if (is_object($object)) {
                $object = get_class($object);
            }
            $parts = explode('\\', $object);

            return (string)end($parts);
        };
    }

    public function arrayValueReader(): callable
    {
        return static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => self::readArrayValue($array, $key, $default);
    }

    private static function readArrayValue(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed
    {
        if ($key === null) {
            return $default;
        }
        if (is_array($key)) {
            if ($key === []) {
                return $default;
            }
            $firstKey = array_shift($key);
            if ($key !== []) {
                return isset($array[$firstKey]) && (is_array($array[$firstKey]) || $array[$firstKey] instanceof \ArrayAccess)
                    ? self::readArrayValue($array[$firstKey], $key, $default)
                    : $default;
            }
            $key = $firstKey;
        }

        return $array[$key] ?? $default;
    }
}

final class BaseServiceDelegateDouble
{
    public array $calls = [];

    public function doWork(string $value, int $count): string
    {
        $this->calls[] = [$value, $count];

        return 'processed:' . $value . ':' . $count;
    }
}

final class BaseServiceEngineDouble
{
    public ?object $facade = null;

    public function setFacade(object $facade): void
    {
        $this->facade = $facade;
    }
}

<?php

declare(strict_types=1);

use fan\core\service\plain;
use FanTest\core\SourceFileContractTestCase;
use fan\core\base\service;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;


class ServicePlainTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/plain.php';

    public function testAddHeaderAndSetHeadersMutateKnownHeaderKeys(): void
    {
        $plain = new ServicePlainProbe();

        $this->assertSame($plain, $plain->addHeader('contentType', 'application/json'));
        $this->assertSame($plain, $plain->setHeaders([
            'response' => 201,
            'encoding' => 'charset=utf-8',
        ]));

        $this->assertSame(201, $plain->headers()['response']);
        $this->assertSame('application/json', $plain->headers()['contentType']);
        $this->assertSame('charset=utf-8', $plain->headers()['encoding']);
    }

    public function testSetErrorMessageStoresValidHttpErrorState(): void
    {
        $plain = new ServicePlainProbe();

        $this->assertFalse($plain->isError());
        $this->assertSame($plain, $plain->setErrorMessage('Not found', 404));

        $this->assertTrue($plain->isError());
        $this->assertSame(404, $plain->errorCode());
        $this->assertSame('Not found', $plain->errorMessage());
    }

    public function testInvalidHeaderUsesInjectedServiceExceptionFactory(): void
    {
        $runtime = new ServicePlainRuntimeDouble();
        $plain = new ServicePlainProbe(runtime: $runtime);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown header key "bad"');

        try {
            $plain->addHeader('bad', 'value');
        } finally {
            $this->assertSame([
                ['\fan\project\exception\service\fatal', $plain, 'Unknown header key "bad"', E_USER_ERROR, null],
            ], $runtime->serviceExceptionFactoryCalls);
        }
    }

    public function testGetFinalContentReturnsControllerResultAndAssignsHeaders(): void
    {
        $plain = new ServicePlainProbe();
        $plain->setController(new ServicePlainControllerDouble('payload'));

        $this->assertSame('payload', $plain->exposeGetFinalContent('content'));
        $this->assertSame($plain->headers(), $plain->assignedHeaders);
    }

    public function testGetFinalContentConvertsErrorToPlain404Response(): void
    {
        $plain = new ServicePlainProbe();
        $plain->setController(new ServicePlainControllerDouble('ignored'));
        $plain->setErrorMessage('Missing page', 404);

        $this->assertSame('Missing page', $plain->exposeGetFinalContent('content'));
        $this->assertSame(404, $plain->assignedHeaders['response']);
        $this->assertSame('text/plain', $plain->assignedHeaders['contentType']);
        $this->assertSame('charset=utf-8', $plain->assignedHeaders['encoding']);
        $this->assertSame('Error 404', $plain->assignedHeaders['filename']);
        $this->assertSame(strlen('Missing page'), $plain->assignedHeaders['length']);
    }

    public function testGetHandleDataReturnsCurrentMatcherHandlerArray(): void
    {
        $plain = new ServicePlainProbe();
        $plain->setMatcher(new ServicePlainMatcherDouble(['method' => 'show', 'param' => ['id' => 7]]));

        $this->assertSame(['method' => 'show', 'param' => ['id' => 7]], $plain->getHandleData());
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseHelper();
        $runtime = new ServicePlainRuntimeDouble();
        $configurator = new ServicePlainConfiguratorDouble(new ServicePlainBaseConfigDouble());
        $cacheFactoryCalls = [];
        $matcher = new ServicePlainMatcherDouble(['method' => 'show', 'param' => []]);
        $header = new ServicePlainHeaderDouble();

        $plain = new ServicePlainConstructorProbe(
            true,
            $matcher,
            static fn(): object => new ServicePlainConfigDouble(),
            $header,
            static fn(): array => [],
            static fn(string $controllerClass, object $plainService, int|string $controllerKey, array $dependencies): object => new $controllerClass($plainService, $controllerKey, ...$dependencies),
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            }
        );

        $this->assertSame([ServicePlainConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$plain], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServicePlainConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame(['method' => 'show', 'param' => []], $plain->getHandleData());
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testSetControllerUsesInjectedPlainConfigFactory(): void
    {
        $plain = new ServicePlainProbe(
            plainConfigFactory: static fn(): object => new ServicePlainConfigDouble(),
            controllerFactory: static fn(string $controllerClass, object $plainService, int|string $controllerKey, array $dependencies): object => new $controllerClass($plainService, $controllerKey, ...$dependencies)
        );

        $plain->exposeSetController(17, ServicePlainConfigurableControllerDouble::class);

        $this->assertSame(
            ['controller' => ServicePlainConfigurableControllerDouble::class, 'key' => 17],
            $plain->controller()->config
        );
    }

    public function testSetControllerPassesInjectedControllerDependencies(): void
    {
        $dependency = new stdClass();
        $factoryCalls = [];
        $plain = new ServicePlainProbe(
            controllerDependenciesFactory: static fn(string $controllerClass, int|string $key, object $handler): array => [$dependency],
            controllerFactory: function (string $controllerClass, object $plainService, int|string $controllerKey, array $dependencies) use (&$factoryCalls): object {
                $factoryCalls[] = [$controllerClass, $controllerKey, $dependencies];

                return new $controllerClass($plainService, $controllerKey, ...$dependencies);
            }
        );

        $plain->exposeSetController('asset', ServicePlainDependencyControllerDouble::class);

        $this->assertSame($dependency, $plain->controller()->dependency);
        $this->assertSame([
            [ServicePlainDependencyControllerDouble::class, 'asset', [$dependency]],
        ], $factoryCalls);
    }

    private function ensureBaseHelper(): void
    {
        if (function_exists('fan\core\base\get_class_name')) {
            return;
        }

        eval('
            namespace fan\core\base;

            function get_class_name(string|object $object): ?string
            {
                if (is_object($object)) {
                    $object = get_class($object);
                }
                $parts = explode("\\\\", $object);

                return end($parts);
            }
        ');
    }
}

final class ServicePlainProbe extends plain
{
    public ?array $assignedHeaders = null;

    public function __construct(
        ?object $matcher = null,
        ?callable $plainConfigFactory = null,
        ?object $header = null,
        ?callable $controllerDependenciesFactory = null,
        ?callable $controllerFactory = null,
        ?object $runtime = null
    )
    {
        $this->matcher = $matcher;
        $this->plainConfigFactory = $plainConfigFactory;
        $this->header = $header;
        $this->controllerDependenciesFactory = $controllerDependenciesFactory;
        $this->controllerFactory = $controllerFactory;
        if ($runtime !== null) {
            $this->setServiceDependencies(serviceBootstrapRuntime: $runtime);
        }
    }

    public function setController(object $controller): void
    {
        $this->controller = $controller;
    }

    public function setMatcher(object $matcher): void
    {
        $this->matcher = $matcher;
    }

    public function exposeGetFinalContent(string $method): mixed
    {
        return $this->_getFinalContent($method);
    }

    public function exposeSetController(int|string $key, string $controller): void
    {
        $this->_setController($key, $controller);
    }

    public function controller(): object
    {
        return $this->controller;
    }

    public function headers(): array
    {
        $property = new ReflectionProperty(plain::class, 'headers');
        return $property->getValue($this);
    }

    public function errorCode(): int|float|null
    {
        $property = new ReflectionProperty(plain::class, 'errCode');
        return $property->getValue($this);
    }

    public function errorMessage(): ?string
    {
        $property = new ReflectionProperty(plain::class, 'errMsg');
        return $property->getValue($this);
    }

    protected function _assignHeaders(): static
    {
        $this->assignedHeaders = $this->headers();

        return $this;
    }
}

final class ServicePlainConstructorProbe extends plain
{
    public function __construct(
        bool $allowIni,
        ?object $matcher,
        ?callable $plainConfigFactory,
        ?object $header,
        ?callable $controllerDependenciesFactory,
        ?callable $controllerFactory,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory
    )
    {
        parent::__construct(
            $allowIni,
            $matcher,
            $plainConfigFactory,
            $header,
            $controllerDependenciesFactory,
            $controllerFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory
        );
    }
}

final class ServicePlainControllerDouble
{
    public function __construct(private mixed $content)
    {
    }

    public function content(): mixed
    {
        return $this->content;
    }
}

final class ServicePlainConfigurableControllerDouble
{
    public mixed $config = null;

    public function __construct(object $plain, int|string $key)
    {
    }

    public function setConfig(mixed $config): void
    {
        $this->config = $config;
    }
}

final class ServicePlainDependencyControllerDouble
{
    public function __construct(object $plain, int|string $key, public object $dependency)
    {
    }
}

final class ServicePlainConfigDouble
{
    public function getControllerConfig(object $controller, int|string $key): array
    {
        return ['controller' => get_class($controller), 'key' => $key];
    }
}

final class ServicePlainHeaderDouble
{
    public array $headers = [];

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }
}

final class ServicePlainRuntimeDouble
{
    public ServicePlainInitializerDouble $initializer;
    public array $serviceExceptionFactoryCalls = [];

    public function __construct()
    {
        $this->initializer = new ServicePlainInitializerDouble();
    }

    public function getInitializer(): ServicePlainInitializerDouble
    {
        return $this->initializer;
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

    public function serviceExceptionFactory(): callable
    {
        return function (
            string $exceptionClass,
            service $service,
            string $message,
            int $code = E_USER_ERROR,
            ?\Throwable $previous = null
        ): \Throwable {
            $this->serviceExceptionFactoryCalls[] = [$exceptionClass, $service, $message, $code, $previous];

            return new \RuntimeException($message, $code, $previous);
        };
    }
}

final class ServicePlainInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServicePlainConfiguratorDouble
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

final class ServicePlainBaseConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}

final class ServicePlainMatcherDouble
{
    public function __construct(private array $handlerData)
    {
    }

    public function getCurrentItem(): object
    {
        return new class($this->handlerData) {
            public object $handler;

            public function __construct(array $handlerData)
            {
                $this->handler = new class($handlerData) {
                    public function __construct(private array $handlerData)
                    {
                    }

                    public function toArray(): array
                    {
                        return $this->handlerData;
                    }
                };
            }
        };
    }
}

<?php

declare(strict_types=1);

use fan\core\di\request_service_factory;
use fan\core\service\request;
use PHPUnit\Framework\TestCase;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;


if (!function_exists('get_class_name')) {
    function get_class_name(string|object $object): ?string
    {
        if (is_object($object)) {
            $object = get_class($object);
        }

        $parts = explode('\\', $object);

        return end($parts);
    }
}

if (!function_exists('array_merge_recursive_alt')) {
    function array_merge_recursive_alt(mixed $arrFirst): mixed
    {
        if (!is_array($arrFirst)) {
            $arrFirst = $arrFirst === null ? [] : [$arrFirst];
        }
        foreach (array_slice(func_get_args(), 1) as $arrNext) {
            if ($arrNext === null) {
                continue;
            }
            $arrNext = is_array($arrNext) ? $arrNext : [$arrNext];
            foreach ($arrNext as $key => $value) {
                $arrFirst[$key] = isset($arrFirst[$key]) && (is_array($arrFirst[$key]) || is_array($value))
                    ? array_merge_recursive_alt($arrFirst[$key], $value)
                    : $value;
            }
        }
        return $arrFirst;
    }
}

final class RequestServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreRequestServiceWithTypedConstructor(): void
    {
        $overrideCalls = [];
        $input = new RequestServiceFactoryInputDouble(
            get: ['page' => '2'],
            server: ['REQUEST_METHOD' => 'GET']
        );
        $runtime = new RequestServiceFactoryRuntimeDouble();
        $configurator = new RequestServiceFactoryConfiguratorDouble(new RequestServiceFactoryConfigDouble());
        $jsonFactory = static fn(bool $useBase64 = false): object => new RequestServiceFactoryJsonDouble($useBase64);
        $cookieFactory = static fn(): object => new RequestServiceFactoryCookieDouble(['sid' => 'abc']);
        $matcherFactory = static fn(): object => new RequestServiceFactoryMatcherDouble();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $arrayAdducer = static fn(mixed $value): array => is_array($value) ? $value : [$value];
        $recursiveMerger = static fn(mixed ...$values): mixed => array_replace_recursive(...array_map(
            static fn(mixed $value): array => is_array($value) ? $value : [$value],
            $values
        ));
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $classNameResolver = static fn(object $object): string => get_class($object);
        $factory = new request_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        );

        $request = $factory(
            request::class,
            $input,
            $runtime,
            $jsonFactory,
            $cookieFactory,
            $matcherFactory,
            $runtime,
            $configurator,
            $cacheFactory,
            $arrayAdducer,
            $recursiveMerger,
            $arrayValueReader,
            $classNameResolver
        );

        $this->assertInstanceOf(request::class, $request);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([request::class], $runtime->initializer->serviceParams);
        $this->assertSame([$request], $configurator->getServiceConfigCalls);
        $this->assertSame([[request::class, 'ENABLED']], $configurator->resetCalls);
        $this->assertSame('GET', $request->get('REQUEST_METHOD', 'S'));
        $this->assertSame(['sid' => 'abc'], $request->getAll('C'));
    }

    public function testFactoryDelegatesConfiguredRequestServiceOverrides(): void
    {
        $calls = [];
        $input = new stdClass();
        $runtime = new stdClass();
        $jsonFactory = static fn(bool $useBase64 = false): object => (object)['useBase64' => $useBase64];
        $cookieFactory = static fn(): object => new stdClass();
        $matcherFactory = static fn(): object => new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $arrayAdducer = static fn(mixed $value): array => is_array($value) ? $value : [$value];
        $recursiveMerger = static fn(mixed ...$values): mixed => array_replace_recursive(...array_map(
            static fn(mixed $value): array => is_array($value) ? $value : [$value],
            $values
        ));
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $classNameResolver = static fn(object $object): string => get_class($object);
        $factory = new request_service_factory(
            static function (string $className, array $arguments) use (&$calls): object {
                $calls[] = [$className, $arguments];

                return new RequestServiceFactoryProbe(...$arguments);
            }
        );

        $request = $factory(
            RequestServiceFactoryProbe::class,
            $input,
            $runtime,
            $jsonFactory,
            $cookieFactory,
            $matcherFactory,
            $runtime,
            $configurator,
            $cacheFactory,
            $arrayAdducer,
            $recursiveMerger,
            $arrayValueReader,
            $classNameResolver
        );

        $this->assertInstanceOf(RequestServiceFactoryProbe::class, $request);
        $this->assertSame(RequestServiceFactoryProbe::class, $calls[0][0]);
        $this->assertSame([
            $input,
            $runtime,
            $jsonFactory,
            $cookieFactory,
            $matcherFactory,
            $runtime,
            $configurator,
            $cacheFactory,
            $arrayAdducer,
            $recursiveMerger,
            $arrayValueReader,
            $classNameResolver,
        ], $calls[0][1]);
        $this->assertSame($input, $request->input);
        $this->assertSame($runtime, $request->runtime);
        $this->assertSame($jsonFactory, $request->jsonFactory);
        $this->assertSame($cookieFactory, $request->cookieFactory);
        $this->assertSame($matcherFactory, $request->matcherFactory);
        $this->assertSame($configurator, $request->serviceConfigurator);
        $this->assertSame($cacheFactory, $request->serviceCacheFactory);
        $this->assertSame($arrayAdducer, $request->arrayAdducer);
        $this->assertSame($recursiveMerger, $request->recursiveMerger);
        $this->assertSame($arrayValueReader, $request->arrayValueReader);
        $this->assertSame($classNameResolver, $request->classNameResolver);
    }

            }

final class RequestServiceFactoryProbe
{
    public function __construct(
        public object $input,
        public object $runtime,
        public $jsonFactory,
        public $cookieFactory,
        public $matcherFactory,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory,
        public $arrayAdducer,
        public $recursiveMerger,
        public $arrayValueReader,
        public $classNameResolver
    ) {
    }
}


final class RequestServiceFactoryRuntimeDouble
{
    public RequestServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new RequestServiceFactoryInitializerDouble();
    }

    public function isCli(): bool
    {
        return false;
    }

    public function getInitializer(): RequestServiceFactoryInitializerDouble
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

    public function classNameResolver(): callable
    {
        return static fn(object|string $object): string => get_class_name($object) ?? (is_object($object) ? get_class($object) : $object);
    }
}

final class RequestServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class RequestServiceFactoryConfiguratorDouble
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

final class RequestServiceFactoryConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return match ($key) {
            'ALLOW_SET' => ['G' => '_GET', 'P' => '_POST', 'R' => '_REQUEST'],
            'DEFAULT_ORDER' => 'PAG',
            default => $default,
        };
    }
}

final class RequestServiceFactoryInputDouble
{
    public function __construct(
        private array $get = [],
        private array $server = [],
        private array $argv = [],
        private string $rawPost = ''
    ) {
    }

    public function globalArray(string $name): array
    {
        return match ($name) {
            '_GET' => $this->get,
            '_SERVER' => $this->server,
            'argv' => $this->argv,
            default => [],
        };
    }

    public function unsetGlobalValue(string $name, mixed $key): void
    {
    }

    public function get(): array
    {
        return $this->get;
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function headers(): array
    {
        return [];
    }

    public function argv(): array
    {
        return $this->argv;
    }

    public function rawPost(): string
    {
        return $this->rawPost;
    }
}

final class RequestServiceFactoryJsonDouble
{
    public function __construct(private bool $useBase64 = false)
    {
    }

    public function decode(string $payload): mixed
    {
        return json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
    }
}

final class RequestServiceFactoryCookieDouble
{
    public function __construct(private array $cookies = [])
    {
    }

    public function getAll(): array
    {
        return $this->cookies;
    }
}

final class RequestServiceFactoryMatcherDouble
{
    public function getCurrentIndex(): int
    {
        return -1;
    }

    public function getCurrentItem(): object
    {
        return (object)[
            'parsed' => (object)[
                'add_request' => [],
                'main_request' => [],
                'query' => '',
            ],
        ];
    }

    public function getItem(int $index): object
    {
        return $this->getCurrentItem();
    }
}

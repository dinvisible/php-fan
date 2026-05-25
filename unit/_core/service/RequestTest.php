<?php

declare(strict_types=1);

use fan\core\service\request;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;


class ServiceRequestTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/request.php';

    public function testGetRespectsExplicitLookupOrder(): void
    {
        $request = $this->request([
            'P' => ['id' => 'post'],
            'G' => ['id' => 'get'],
            'A0' => [],
        ]);

        $this->assertSame('post', $request->get('id', 'PG'));
        $this->assertSame('get', $request->get('id', 'GP'));
        $this->assertSame('fallback', $request->get('missing', 'PG', 'fallback'));
    }

    public function testMagicGetterAndInvokeDelegateToGet(): void
    {
        $request = $this->request([
            'P' => ['token' => 'abc'],
            'G' => [],
            'A0' => [],
        ]);

        $this->assertSame('abc', $request->token);
        $this->assertSame('abc', $request('token', 'P'));
    }

    public function testGetAllMergesSourcesInDeclaredOrder(): void
    {
        $request = $this->request([
            'P' => ['shared' => 'post', 'postOnly' => true],
            'G' => ['shared' => 'get', 'getOnly' => true],
            'A0' => [],
        ]);

        $this->assertSame([
            'shared' => 'get',
            'postOnly' => true,
            'getOnly' => true,
        ], $request->getAll('GP'));
    }

    public function testInvalidOrderUsesInjectedServiceExceptionFactory(): void
    {
        $request = $this->request();
        $factoryCalls = [];
        $request->setServiceDependencies(
            serviceExceptionFactory: static function (
                string $exceptionClass,
                object $service,
                string $message,
                int $code,
                ?Throwable $previous
            ) use (&$factoryCalls): Throwable {
                $factoryCalls[] = [$exceptionClass, $service, $message, $code, $previous];

                return new RuntimeException($message, $code, $previous);
            }
        );

        try {
            $request->get('id', 'Z');
            $this->fail('Expected injected service exception factory to create the invalid order failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Incorrect symbols in order "Z". Possible symbols "A0A1BCEFGHMOPRS".', $exception->getMessage());
        }

        $this->assertCount(1, $factoryCalls);
        $this->assertSame('\fan\project\exception\service\fatal', $factoryCalls[0][0]);
        $this->assertSame($request, $factoryCalls[0][1]);
        $this->assertSame('Incorrect symbols in order "Z". Possible symbols "A0A1BCEFGHMOPRS".', $factoryCalls[0][2]);
        $this->assertSame(E_USER_ERROR, $factoryCalls[0][3]);
        $this->assertNull($factoryCalls[0][4]);
    }

    public function testSetAndRemoveMutateAllowedRequestBuckets(): void
    {
        $input = new ServiceRequestInputDouble();
        $request = $this->request([
            'P' => [],
            'G' => ['removeMe' => 'yes'],
            'R' => [],
            'A0' => [],
        ], $input);

        $request->set('new', 'value', 'P');
        $request->remove('removeMe', 'G', true);

        $this->assertSame('value', $request->get('new', 'P'));
        $this->assertSame('fallback', $request->get('removeMe', 'G', 'fallback'));
        $this->assertSame([['_GET', 'removeMe']], $input->unsetCalls);
    }

    public function testQueryStringUsesInjectedInputGetDataWhenCurrentMatcherDataIsNotRequested(): void
    {
        $request = $this->request([], new ServiceRequestInputDouble(get: [
            'page' => '2',
            'filter' => 'active',
        ]), matcher: new stdClass());

        $this->assertSame('page=2;filter=active', $request->getQueryString(true, false, ';'));
    }

    public function testGetInfoStringIncludesKnownServerKeys(): void
    {
        $input = new ServiceRequestInputDouble(server: [
            'HTTP_HOST' => 'example.test',
            'REQUEST_METHOD' => 'GET',
            'REMOTE_ADDR' => '127.0.0.1',
        ]);

        $info = $this->request(input: $input)->getInfoString();

        $this->assertStringContainsString('HTTP_HOST = example.test;', $info);
        $this->assertStringContainsString('REQUEST_METHOD = GET;', $info);
        $this->assertStringContainsString('REMOTE_ADDR = 127.0.0.1;', $info);
    }

    public function testRawPostJsonUsesInjectedJsonFactory(): void
    {
        $jsonFactory = new ServiceRequestJsonFactoryDouble();
        $request = $this->request(
            input: new ServiceRequestInputDouble(rawPost: '{"answer":42}'),
            jsonFactory: $jsonFactory(...)
        );

        $this->assertSame(['answer' => 42], $request->getRawPost('json', true));
        $this->assertSame([true], $jsonFactory->useBase64Calls);
    }

    public function testRawPostXmlIsConvertedWithoutDeclaringRuntimeFunction(): void
    {
        $request = $this->request(
            input: new ServiceRequestInputDouble(rawPost: '<payload><answer>42</answer><nested><item>ok</item></nested></payload>')
        );

        $this->assertSame([
            'answer' => '42',
            'nested' => ['item' => 'ok'],
        ], $request->getRawPost('xml'));
    }

    public function testCookieDataUsesInjectedCookieFactory(): void
    {
        $request = $this->request(cookieFactory: fn(): object => new ServiceRequestCookieDouble([
            'sid' => 'cookie-session',
        ]));

        $this->assertSame(['sid' => 'cookie-session'], $request->exposeMakeCookies());
    }

    public function testMatcherIsLoadedFromInjectedFactoryOnlyWhenNeeded(): void
    {
        $matcher = new ServiceRequestMatcherDouble();
        $factoryCalls = 0;
        $request = $this->request(matcher: null, matcherFactory: function () use ($matcher, &$factoryCalls): object {
            $factoryCalls++;

            return $matcher;
        });

        $this->assertSame($matcher, $request->exposeGetMatcher());
        $this->assertSame($matcher, $request->exposeGetMatcher());
        $this->assertSame(1, $factoryCalls);
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseFunctionAliases();

        $input = new ServiceRequestInputDouble(
            get: ['page' => '2'],
            server: ['REQUEST_METHOD' => 'GET']
        );
        $runtime = new ServiceRequestRuntimeDouble();
        $config = new ServiceRequestConfigDouble();
        $configurator = new ServiceRequestConfiguratorDouble($config);
        $cacheFactoryCalls = [];

        $request = new ServiceRequestConstructorProbe(
            $input,
            $runtime,
            fn(bool $useBase64 = false): object => new ServiceRequestJsonDouble($useBase64),
            fn(): object => new ServiceRequestCookieDouble(['sid' => 'abc']),
            fn(): object => new ServiceRequestMatcherDouble(),
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            },
            $this->arrayAdducer(),
            $this->recursiveMerger(),
            $this->arrayValueReader(),
            static fn(object $object): string => get_class($object)
        );

        $this->assertSame([ServiceRequestConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$request], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceRequestConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame('GET', $request->get('REQUEST_METHOD', 'S'));
        $this->assertSame(['sid' => 'abc'], $request->getAll('C'));
        $this->assertSame([], $cacheFactoryCalls);
    }

    private function request(
        array $data = [],
        ?ServiceRequestInputDouble $input = null,
        mixed $matcher = null,
        ?callable $jsonFactory = null,
        ?callable $cookieFactory = null,
        ?callable $matcherFactory = null
    ): ServiceRequestProbe
    {
        $request = (new ReflectionClass(ServiceRequestProbe::class))->newInstanceWithoutConstructor();
        $allData = [
            'A0' => [],
            'A1' => [],
            'B' => [],
            'C' => [],
            'E' => [],
            'F' => [],
            'G' => [],
            'H' => [],
            'M' => [],
            'O' => [],
            'P' => [],
            'R' => [],
            'S' => [],
        ];

        foreach ([
            'data' => array_replace($allData, $data),
            'order' => 'PAG',
            'matcher' => $matcher,
            'runtime' => new ServiceRequestRuntimeDouble(),
            'jsonFactory' => $jsonFactory ?? fn(bool $useBase64 = false): object => new ServiceRequestJsonDouble($useBase64),
            'cookieFactory' => $cookieFactory ?? fn(): object => new ServiceRequestCookieDouble(),
            'matcherFactory' => $matcherFactory,
            'maker' => [],
            'config' => new ServiceRequestConfigDouble(),
            'input' => $input ?? new ServiceRequestInputDouble(),
            'arrayAdducer' => $this->arrayAdducer(),
            'recursiveMerger' => $this->recursiveMerger(),
            'arrayValueReader' => $this->arrayValueReader(),
        ] as $propertyName => $value) {
            $property = $this->property(request::class, $propertyName);
            $property->setValue($request, $value);
        }

        return $request;
    }

    private function property(string $class, string $propertyName): ReflectionProperty
    {
        do {
            if (property_exists($class, $propertyName)) {
                return new ReflectionProperty($class, $propertyName);
            }
            $class = get_parent_class($class);
        } while ($class);

        throw new LogicException('Property not found: ' . $propertyName);
    }

    private function arrayAdducer(): callable
    {
        return static fn(mixed $value): array => is_array($value) ? $value : [$value];
    }

    private function recursiveMerger(): callable
    {
        $merge = null;
        $merge = static function (mixed $first, mixed ...$rest) use (&$merge): mixed {
            $result = is_array($first) ? $first : ($first === null ? [] : [$first]);
            foreach ($rest as $next) {
                if ($next === null) {
                    continue;
                }
                foreach ((is_array($next) ? $next : [$next]) as $key => $value) {
                    $result[$key] = isset($result[$key]) && (is_array($result[$key]) || is_array($value))
                        ? $merge($result[$key], $value)
                        : $value;
                }
            }

            return $result;
        };

        return $merge;
    }

    private function arrayValueReader(): callable
    {
        return static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
    }

    private function ensureBaseFunctionAliases(): void
    {
        if (!function_exists('get_class_name')) {
            eval('function get_class_name(string|object $object): ?string { if (is_object($object)) { $object = get_class($object); } $parts = explode("\\\\\\\\", $object); return end($parts); }');
        }
        if (!function_exists('fan\core\base\get_class_name')) {
            eval('namespace fan\core\base { function get_class_name(string|object $object): ?string { return \get_class_name($object); } }');
        }
    }
}

final class ServiceRequestProbe extends request
{
    public function exposeMakeCookies(): array
    {
        return $this->_makeCookies();
    }

    public function exposeGetMatcher(): mixed
    {
        return $this->_getMatcher();
    }
}

final class ServiceRequestConstructorProbe extends request
{
    public function __construct(
        ?object $input,
        ?object $runtime,
        ?callable $jsonFactory,
        ?callable $cookieFactory,
        ?callable $matcherFactory,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory,
        ?callable $arrayAdducer,
        ?callable $recursiveMerger,
        ?callable $arrayValueReader,
        ?callable $classNameResolver
    )
    {
        parent::__construct(
            $input,
            $runtime,
            $jsonFactory,
            $cookieFactory,
            $matcherFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $arrayAdducer,
            $recursiveMerger,
            $arrayValueReader,
            $classNameResolver
        );
    }
}

final class ServiceRequestConfigDouble
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

final class ServiceRequestMatcherDouble
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

final class ServiceRequestRuntimeDouble
{
    public ServiceRequestInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ServiceRequestInitializerDouble();
    }

    public function isCli(): bool
    {
        return false;
    }

    public function getInitializer(): ServiceRequestInitializerDouble
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
}

final class ServiceRequestInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceRequestConfiguratorDouble
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

final class ServiceRequestJsonFactoryDouble
{
    public array $useBase64Calls = [];

    public function __invoke(bool $useBase64): object
    {
        $this->useBase64Calls[] = $useBase64;

        return new ServiceRequestJsonDouble($useBase64);
    }
}

final class ServiceRequestJsonDouble
{
    public function __construct(private bool $useBase64 = false)
    {
    }

    public function decode(string $payload): mixed
    {
        return json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
    }
}

final class ServiceRequestCookieDouble
{
    public function __construct(private array $cookies = [])
    {
    }

    public function getAll(): array
    {
        return $this->cookies;
    }
}

final class ServiceRequestInputDouble
{
    public array $unsetCalls = [];

    public function __construct(
        private array $get = [],
        private array $server = [],
        private string $rawPost = '',
        private array $argv = [],
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
        $this->unsetCalls[] = [$name, $key];
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

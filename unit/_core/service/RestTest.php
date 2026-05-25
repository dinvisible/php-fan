<?php

declare(strict_types=1);

use fan\core\service\curl;
use fan\core\service\rest;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\base\service;


class ServiceRestTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/rest.php';

    public function testGetAppendsArrayDataAsQueryStringAndDisablesSslVerification(): void
    {
        $rest = new ServiceRestProbe();

        $result = $rest->get('users', ['page' => 2, 'filter' => 'new']);

        $this->assertSame('users?page=2&filter=new', $rest->curlSuffixes[0]);
        $this->assertSame([[CURLOPT_SSL_VERIFYPEER, false]], $rest->lastCurl()->options);
        $this->assertSame(['post' => null, 'suffix' => 'users?page=2&filter=new'], $result);
    }

    public function testGetAppendsScalarDataAsUrlEncodedPathSegment(): void
    {
        $rest = new ServiceRestProbe();

        $rest->get('users', 'a b');

        $this->assertSame('users/a+b', $rest->curlSuffixes[0]);
    }

    public function testPostEncodesJsonPayloadAndSetsJsonHeaders(): void
    {
        $rest = new ServiceRestProbe();

        $result = $rest->post('users', ['name' => 'Fan']);

        $this->assertSame('users', $rest->curlSuffixes[0]);
        $this->assertSame([['Content-Type: application/json', 'charset=utf-8']], $rest->lastCurl()->headers);
        $this->assertSame('{"name":"Fan"}', $result['post']);
    }

    public function testPostCanSendRawPayloadWithoutJsonHeaders(): void
    {
        $rest = new ServiceRestProbe();

        $result = $rest->post('users', 'raw-body', 'raw');

        $this->assertSame([], $rest->lastCurl()->headers);
        $this->assertSame('raw-body', $result['post']);
    }

    public function testDeleteAndPutConfigureHttpMethodAndPutPayload(): void
    {
        $rest = new ServiceRestProbe();

        $rest->delete('users', '42');
        $deleteCurl = $rest->lastCurl();

        $this->assertSame('users/42', $rest->curlSuffixes[0]);
        $this->assertContains([CURLOPT_CUSTOMREQUEST, 'DELETE'], $deleteCurl->options);

        $result = $rest->_callPutRequest('users', '42');
        $putCurl = $rest->lastCurl();

        $this->assertSame('users/42', $rest->curlSuffixes[1]);
        $this->assertContains([CURLOPT_CUSTOMREQUEST, 'PUT'], $putCurl->options);
        $this->assertContains([CURLOPT_POSTFIELDS, 'pay_code=42'], $putCurl->options);
        $this->assertNull($result['post']);
    }

    public function testConnectionNameReturnsInjectedConnectionName(): void
    {
        $rest = new ServiceRestProbe('main');

        $this->assertSame('main', $rest->getConnectionName());
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseFunctionAliases();

        $runtime = new ServiceRestRuntimeDouble();
        $config = new ServiceRestConfigDouble([
            'DEFAULT_CONNECTION' => 'main',
            'CONNECTION' => [
                'main' => [
                    'url' => [
                        'server' => 'example.test',
                        'request' => 'api',
                    ],
                ],
            ],
        ]);
        $configurator = new ServiceRestConfiguratorDouble($config);
        $cacheFactoryCalls = [];

        $rest = new ServiceRestConstructorProbe(
            null,
            static fn(): object => new ServiceRestJsonDouble(),
            static fn(string $url): curl => new ServiceRestCurlDouble($url),
            static fn(): object => new ServiceRestErrorDouble(),
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            }
        );

        $this->assertSame([], $runtime->initializer->serviceParams);
        $this->assertSame([$rest], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceRestConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame('main', $rest->getConnectionName());
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testUnknownConnectionUsesInjectedServiceExceptionFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $runtime = new ServiceRestRuntimeDouble();
        $configurator = new ServiceRestConfiguratorDouble(new ServiceRestConfigDouble([
            'DEFAULT_CONNECTION' => 'missing',
            'CONNECTION' => [],
        ]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Undefined connection name <b>missing</b>');

        try {
            new ServiceRestConstructorProbe(
                null,
                static fn(): object => new ServiceRestJsonDouble(),
                static fn(string $url): curl => new ServiceRestCurlDouble($url),
                static fn(): object => new ServiceRestErrorDouble(),
                $runtime,
                $configurator,
                static fn(string $type): object => (object)['type' => $type]
            );
        } finally {
            $this->assertCount(1, $runtime->serviceExceptionFactoryCalls);
            $this->assertSame('\fan\project\exception\service\fatal', $runtime->serviceExceptionFactoryCalls[0][0]);
            $this->assertInstanceOf(rest::class, $runtime->serviceExceptionFactoryCalls[0][1]);
            $this->assertSame('Undefined connection name <b>missing</b>', $runtime->serviceExceptionFactoryCalls[0][2]);
            $this->assertSame(E_USER_ERROR, $runtime->serviceExceptionFactoryCalls[0][3]);
            $this->assertNull($runtime->serviceExceptionFactoryCalls[0][4]);
        }
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

final class ServiceRestProbe extends rest
{
    public array $curlSuffixes = [];

    /**
     * @var ServiceRestCurlDouble[]
     */
    private array $curls = [];

    public function __construct(?string $connectionName = null)
    {
        $property = new ReflectionProperty(rest::class, 'connectionName');
        $property->setValue($this, $connectionName);

        $this->setFactory('jsonFactory', static fn(): object => new ServiceRestJsonDouble());
    }

    public function lastCurl(): ServiceRestCurlDouble
    {
        return $this->curls[array_key_last($this->curls)];
    }

    protected function _getCurl(string $urlSuffix): curl
    {
        $this->curlSuffixes[] = $urlSuffix;
        $curl = new ServiceRestCurlDouble($urlSuffix);
        $this->curls[] = $curl;

        return $curl;
    }

    protected function _getResponse(curl $curl, mixed $post = null): mixed
    {
        return [
            'post' => $post,
            'suffix' => $curl->suffix,
        ];
    }

    private function setFactory(string $propertyName, callable $factory): void
    {
        $property = new ReflectionProperty(rest::class, $propertyName);
        $property->setValue($this, Closure::fromCallable($factory));
    }
}

final class ServiceRestConstructorProbe extends rest
{
    public function __construct(
        ?string $connectionName,
        ?callable $jsonFactory,
        ?callable $curlFactory,
        ?callable $errorFactory,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory
    )
    {
        parent::__construct(
            $connectionName,
            $jsonFactory,
            $curlFactory,
            $errorFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory
        );
    }
}

final class ServiceRestCurlDouble extends curl
{
    public array $options = [];
    public array $headers = [];

    public function __construct(public string $suffix)
    {
    }

    public function setOption(int $key, mixed $val): static
    {
        $this->options[] = [$key, $val];

        return $this;
    }

    public function setHeaders(array $headers = []): static
    {
        $this->headers[] = $headers;

        return $this;
    }
}

final class ServiceRestRuntimeDouble
{
    public ServiceRestInitializerDouble $initializer;
    public array $serviceExceptionFactoryCalls = [];

    public function __construct()
    {
        $this->initializer = new ServiceRestInitializerDouble();
    }

    public function getInitializer(): ServiceRestInitializerDouble
    {
        return $this->initializer;
    }

    public function classNameResolver(): callable
    {
        return static fn(object|string $object): string => is_object($object) ? get_class($object) : $object;
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

final class ServiceRestInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceRestConfiguratorDouble
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

final class ServiceRestConfigDouble extends ArrayObject
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this : ($this[$key] ?? $default);
    }
}

final class ServiceRestJsonDouble
{
    public function encode(mixed $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}

final class ServiceRestErrorDouble
{
}

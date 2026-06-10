<?php

declare(strict_types=1);

use fan\core\service\curl;
use FanTest\core\SourceFileContractTestCase;
use fan\core\base\service;
use fan\core\service\curl_state;


class ServiceCurlTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/curl.php';

    public function testSetTimeoutAndCookiesDelegateToCurlOptions(): void
    {
        $curl = new ServiceCurlProbe();

        $this->assertSame($curl, $curl->setTimeout(15));
        $this->assertSame($curl, $curl->setCookies(['sid' => 'abc', 'mode' => 'debug']));
        $this->assertSame($curl, $curl->setCookies('raw=value'));

        $this->assertSame([
            [CURLOPT_TIMEOUT, 15],
            [CURLOPT_COOKIE, 'sid=abc; mode=debug'],
            [CURLOPT_COOKIE, 'raw=value'],
        ], $curl->options);
    }

    public function testSetHeadersConvertsNonEmptyHeaderListToCurlOption(): void
    {
        $curl = new ServiceCurlProbe();

        $this->assertSame($curl, $curl->setHeaders(['Accept: application/json']));
        $this->assertSame($curl, $curl->setHeaders([]));

        $this->assertSame([
            [CURLOPT_HTTPHEADER, ['Accept: application/json']],
        ], $curl->options);
    }

    public function testGetCookiesParsesSetCookieHeaderAndCanReturnSingleCookie(): void
    {
        $curl = new ServiceCurlProbe();
        $curl->setResponseHeaders([
            'Set-Cookie' => 'sid=abc; Path=/; theme=dark; HttpOnly;',
        ]);

        $this->assertSame(['sid' => 'abc', 'Path' => '/', 'theme' => 'dark'], $curl->getCookies());
        $this->assertSame('abc', $curl->getCookies('sid'));
        $this->assertNull($curl->getCookies('missing'));
    }

    public function testSeparatorDetectionHandlesLfCrLfAndCr(): void
    {
        $curl = new ServiceCurlProbe();

        $this->assertSame("\n", $curl->exposeGetSeparator("HTTP/1.1 200 OK\n\nbody"));
        $this->assertSame("\r\n", $curl->exposeGetSeparator("HTTP/1.1 200 OK\r\n\r\nbody"));
        $this->assertSame("\r", $curl->exposeGetSeparator("HTTP/1.1 200 OK\r\rbody"));
    }

    public function testNestedPostArrayIsFlattenedToCurlFieldSyntax(): void
    {
        $curl = new ServiceCurlProbe();
        $data = [];

        $this->assertSame($curl, $curl->exposeConvPostArray($data, 'user', [
            'name' => 'Fan',
            'roles' => ['admin' => 1],
        ]));

        $this->assertSame([
            'user[name]' => 'Fan',
            'user[roles][admin]' => 1,
        ], $data);
    }

    public function testContentAndResponseHeadersReadInternalState(): void
    {
        $curl = new ServiceCurlProbe();
        $curl->setContent('body');
        $curl->setResponseHeaders(['Content-Type' => 'text/plain']);

        $this->assertSame('body', $curl->getContent());
        $this->assertSame(['Content-Type' => 'text/plain'], $curl->getResponseHeaders());
        $this->assertSame('text/plain', $curl->getResponseHeaders('Content-Type'));
    }

    public function testExecCurlErrorUsesInjectedServiceExceptionFactory(): void
    {
        $runtime = new ServiceCurlRuntimeDouble();
        $adapter = new ServiceCurlAdapterDouble(error: 'timeout');
        $curl = new ServiceCurlConstructorProbe(
            'https://example.test',
            'error-case',
            new curl_state(),
            $runtime,
            new ServiceCurlConfiguratorDouble(new ServiceCurlConfigDouble([
                'CURLOPT_PROXY' => '',
                'CURLOPT_PROXYUSERPWD' => '',
            ])),
            static fn(string $type): object => (object)['type' => $type],
            $adapter,
            $this->arrayAdducer(),
            $this->arrayValueReader()
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('There is CURL error ocured: <b>timeout</b>');

        try {
            $curl->exec();
        } finally {
            $this->assertSame([
                ['\fan\project\exception\service\fatal', $curl, 'There is CURL error ocured: <b>timeout</b>', E_USER_ERROR, null],
            ], $runtime->serviceExceptionFactoryCalls);
            $curl->close();
        }
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $runtime = new ServiceCurlRuntimeDouble();
        $config = new ServiceCurlConfigDouble([
            'CURLOPT_PROXY' => '',
            'CURLOPT_PROXYUSERPWD' => '',
        ]);
        $configurator = new ServiceCurlConfiguratorDouble($config);
        $cacheFactoryCalls = [];

        $curl = new ServiceCurlConstructorProbe(
            'https://example.test',
            'main',
            new curl_state(),
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            },
            new ServiceCurlAdapterDouble(),
            $this->arrayAdducer(),
            $this->arrayValueReader()
        );

        $this->assertSame([ServiceCurlConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$curl], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceCurlConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame([], $cacheFactoryCalls);
        $curl->close();
    }

    private function arrayAdducer(): callable
    {
        return static fn(mixed $value): array => is_array($value) ? $value : [$value];
    }

    private function arrayValueReader(): callable
    {
        return static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
    }
}

final class ServiceCurlProbe extends curl
{
    public array $options = [];

    public function __construct()
    {
        $this->setCurlHelpers();
    }

    private function setCurlHelpers(): void
    {
        $arrayAdducer = new ReflectionProperty(curl::class, 'arrayAdducer');
        $arrayAdducer->setValue($this, static fn(mixed $value): array => is_array($value) ? $value : [$value]);

        $arrayValueReader = new ReflectionProperty(curl::class, 'curlArrayValueReader');
        $arrayValueReader->setValue($this, static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default);
    }

    public function setOption(int $key, mixed $val): static
    {
        $this->options[] = [$key, $val];

        return $this;
    }

    public function setResponseHeaders(array $headers): void
    {
        $property = new ReflectionProperty(curl::class, 'headers');
        $property->setValue($this, $headers);
    }

    public function setContent(?string $content): void
    {
        $property = new ReflectionProperty(curl::class, 'content');
        $property->setValue($this, $content);
    }

    public function exposeGetSeparator(?string $data = null): string
    {
        return $this->_getSeparator($data);
    }

    public function exposeConvPostArray(array &$optData, string $key, array $data): static
    {
        return $this->_convPostArray($optData, $key, $data);
    }
}

final class ServiceCurlConstructorProbe extends curl
{
    public function __construct(
        string $url,
        int|float|string $index,
        ?object $state,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory,
        ?object $curlAdapter,
        ?callable $arrayAdducer,
        ?callable $arrayValueReader
    )
    {
        parent::__construct($url, $index, $state, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory, $curlAdapter, $arrayAdducer, $arrayValueReader);
    }

    public function setOption(int $key, mixed $val): static
    {
        return $this;
    }

    public function close(): static
    {
        $this->curl = null;

        return $this;
    }
}

final class ServiceCurlRuntimeDouble
{
    public ServiceCurlInitializerDouble $initializer;
    public array $serviceExceptionFactoryCalls = [];

    public function __construct()
    {
        $this->initializer = new ServiceCurlInitializerDouble();
    }

    public function getInitializer(): ServiceCurlInitializerDouble
    {
        return $this->initializer;
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
        return static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
    }
}

final class ServiceCurlAdapterDouble
{
    public array $closed = [];

    public function __construct(private string $error = '')
    {
    }

    public function init(string $url): object
    {
        return (object)['url' => $url];
    }

    public function setOption(object $handle, int $key, mixed $value): bool
    {
        return true;
    }

    public function close(object $handle): void
    {
        $this->closed[] = $handle;
    }

    public function getInfo(object $handle, int|float|null $option = null): mixed
    {
        return $option === null ? [] : null;
    }

    public function error(object $handle): string
    {
        return $this->error;
    }

    public function exec(object $handle): string|bool
    {
        return false;
    }
}

final class ServiceCurlInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceCurlConfiguratorDouble
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

final class ServiceCurlConfigDouble extends ArrayObject
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this : ($this[$key] ?? $default);
    }
}

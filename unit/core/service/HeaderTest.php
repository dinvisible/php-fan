<?php

declare(strict_types=1);

use fan\core\service\header;
use FanTest\core\SourceFileContractTestCase;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;


class ServiceHeaderTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/header.php';

    public function testClearHeadersInitializesProtocolAndDefaultResponse(): void
    {
        $header = new ServiceHeaderProbe(new ServiceHeaderInputDouble([
            'SERVER_PROTOCOL' => 'HTTP/2',
        ]));

        $previous = $header->clearHeaders();

        $this->assertSame([], $previous);
        $this->assertSame('HTTP/2', $header->getProtocol());
        $this->assertSame(200, $header->getResponseCode());
        $this->assertSame([
            'protocol' => 'HTTP/2',
            'response' => 200,
        ], $header->getHeader());
    }

    public function testSetHeadersReplacesExistingHeaderStack(): void
    {
        $header = new ServiceHeaderProbe();
        $header->clearHeaders();
        $header->addHeader('contentType', 'text/plain');

        $header->setHeaders([
            'response' => 404,
            'filename' => 'missing.txt',
            'length' => 12,
        ]);

        $this->assertSame(404, $header->getResponseCode());
        $this->assertSame('missing.txt', $header->filename);
        $this->assertSame(12, $header->getHeader('length'));
        $this->assertArrayNotHasKey('contentType', $header->getHeader());
    }

    public function testMagicSetterAndRemoveHeaderOperateOnStack(): void
    {
        $header = new ServiceHeaderProbe();
        $header->clearHeaders();

        $header->contentType = 'application/json';
        $header->encoding = 'charset=utf-8';
        $header->removeHeader('encoding');

        $this->assertSame('application/json', $header->contentType);
        $this->assertArrayNotHasKey('encoding', $header->getHeader());
    }

    public function testResponseShortcutsSetKnownStatusCodes(): void
    {
        $header = new ServiceHeaderProbe();
        $header->clearHeaders();

        $this->assertSame($header, $header->error403());
        $this->assertSame(403, $header->getResponseCode());

        $header->error500();
        $this->assertSame(500, $header->getResponseCode());

        $header->ok200();
        $this->assertSame(200, $header->getResponseCode());
    }

    public function testInvalidHeaderParameterUsesInjectedServiceExceptionFactory(): void
    {
        $header = new ServiceHeaderProbe();
        $factoryCalls = [];
        $header->setServiceDependencies(
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
            $header->addHeader('x-unknown', 'value');
            $this->fail('Expected injected service exception factory to create the header parameter failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Incorrect header parameter "x-unknown" for stack', $exception->getMessage());
        }

        $this->assertCount(1, $factoryCalls);
        $this->assertSame('\fan\project\exception\service\fatal', $factoryCalls[0][0]);
        $this->assertSame($header, $factoryCalls[0][1]);
        $this->assertSame('Incorrect header parameter "x-unknown" for stack', $factoryCalls[0][2]);
        $this->assertSame(E_USER_ERROR, $factoryCalls[0][3]);
        $this->assertNull($factoryCalls[0][4]);
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseHelper();
        $runtime = new ServiceHeaderRuntimeDouble();
        $configurator = new ServiceHeaderConfiguratorDouble(new ServiceHeaderConfigDouble());
        $headerWriter = new ServiceHeaderWriterDouble();
        $cacheFactoryCalls = [];

        $header = new ServiceHeaderConstructorProbe(
            true,
            new ServiceHeaderInputDouble([
                'SERVER_PROTOCOL' => 'HTTP/3',
            ]),
            $headerWriter,
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            }
        );

        $this->assertSame([ServiceHeaderConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$header], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceHeaderConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame('HTTP/3', $header->getProtocol());
        $this->assertSame(200, $header->getResponseCode());
        $this->assertSame([], $cacheFactoryCalls);
        $this->assertSame([], $headerWriter->headers);
    }

    public function testUnknownResponseCodeUsesInjectedRecursiveMerger(): void
    {
        $mergeCalls = [];
        $header = new ServiceHeaderProbe(
            recursiveMerger: static function (array $current, array $loaded) use (&$mergeCalls): array {
                $mergeCalls[] = [$current, $loaded];

                return $current + $loaded + [299 => 'Injected'];
            }
        );

        $header->setResponseType(299);

        $this->assertSame(299, $header->getResponseCode());
        $this->assertSame([
            [
                [
                    200 => 'OK',
                    403 => 'Forbidden',
                    404 => 'Not Found',
                    500 => 'Internal Server Error',
                ],
                [299 => 'Loaded'],
            ],
        ], $mergeCalls);
    }

    public function testMissingRecursiveMergerFailsAtUseTime(): void
    {
        $runtime = new ServiceHeaderRuntimeDouble();
        $header = new ServiceHeaderConstructorProbe(
            true,
            new ServiceHeaderInputDouble(),
            new ServiceHeaderWriterDouble(),
            $runtime,
            new ServiceHeaderConfiguratorDouble(new ServiceHeaderConfigDouble()),
            static fn(string $type): object => (object)['type' => $type],
            null
        );
        $method = new ReflectionMethod(header::class, 'recursiveMerger');
        $recursiveMerger = $method->invoke($header);

        $this->assertInstanceOf(Closure::class, $recursiveMerger);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Recursive merger is not configured for header service.');

        $recursiveMerger([], []);
    }

    public function testHeaderSendersUseInjectedWriter(): void
    {
        $writer = new ServiceHeaderWriterDouble();
        $header = new ServiceHeaderProbe(new ServiceHeaderInputDouble([
            'SERVER_PROTOCOL' => 'HTTP/1.0',
        ]), $writer);
        $header->clearHeaders();

        $header->sendResponseType(404);
        $header->sendContentType('application/json', 'charset=utf-8');
        $header->sendLength(123);
        $header->sendTime(0, 3600);
        $header->sendFilename('report.txt', false);
        $header->sendLocation('/next?x=1&amp;y=2', true);
        $header->sendLocation301('/moved?x=1&amp;y=2', true);
        $header->sendArbitrary('X-Debug', 'ok', 'mode=test');

        $this->assertSame([
            ['HTTP/1.0 404 Not Found', true, 0],
            ['Content-Type: application/json; charset=utf-8', true, 0],
            ['Accept-Ranges: bytes', true, 0],
            ['Content-Length: 123', true, 0],
            ['Last-Modified: Thu, 01 Jan 1970 00:00:00 GMT', true, 0],
            ['Expires: Thu, 01 Jan 1970 01:00:00 GMT', true, 0],
            ['Cache-Control: post-check=1,pre-check=1', true, 0],
            ['Content-Disposition: attachment; filename="report.txt"', true, 0],
            ['Location: /next?x=1&y=2', true, 0],
            ['Location: /moved?x=1&y=2', true, 301],
            ['X-Debug: ok; mode=test', true, 0],
        ], $writer->headers);
    }

    public function testSecurityHeadersRejectResponseSplitting(): void
    {
        $writer = new ServiceHeaderWriterDouble();
        $header = new ServiceHeaderProbe(headerWriter: $writer);

        $header->sendSecurityHeaders([
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ]);

        $this->assertSame([
            ['X-Content-Type-Options: nosniff', true, 0],
            ['Referrer-Policy: strict-origin-when-cross-origin', true, 0],
        ], $writer->headers);

        $this->expectException(InvalidArgumentException::class);
        $header->sendSecurityHeaders(['X-Test' => "ok\r\nInjected: value"]);
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

final class ServiceHeaderProbe extends header
{
    public function __construct(
        ?ServiceHeaderInputDouble $input = null,
        ?ServiceHeaderWriterDouble $headerWriter = null,
        ?callable $recursiveMerger = null
    )
    {
        $this->input = $input ?? new ServiceHeaderInputDouble();
        $this->headerWriter = $headerWriter ?? new ServiceHeaderWriterDouble();
        $reflection = new ReflectionProperty(header::class, 'recursiveMerger');
        $reflection->setValue(
            $this,
            Closure::fromCallable(
                $recursiveMerger ?? static fn(array $current, array $loaded): array => $current + $loaded
            )
        );
    }

    protected function _getEngine($name, $object = true): mixed
    {
        return $name === 'code' ? ServiceHeaderCodeDouble::class : parent::_getEngine($name, $object);
    }
}

final class ServiceHeaderConstructorProbe extends header
{
    public function __construct(
        bool $allowIni,
        ?object $input,
        ?object $headerWriter,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory,
        ?callable $recursiveMerger = null
    )
    {
        parent::__construct($allowIni, $input, $headerWriter, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory, $recursiveMerger);
    }
}

final class ServiceHeaderCodeDouble
{
    public static function getCodes2(): array
    {
        return [299 => 'Loaded'];
    }
}

final class ServiceHeaderWriterDouble
{
    public array $headers = [];

    public function send(string $header, bool $replace = true, int $responseCode = 0): void
    {
        $this->headers[] = [$header, $replace, $responseCode];
    }

    public function sent(?string &$file = null, ?int &$line = null): bool
    {
        return false;
    }
}

final class ServiceHeaderInputDouble
{
    public function __construct(private array $server = [])
    {
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }
}

final class ServiceHeaderRuntimeDouble
{
    public ServiceHeaderInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ServiceHeaderInitializerDouble();
    }

    public function getInitializer(): ServiceHeaderInitializerDouble
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
        return static fn(object $object): string => get_class($object);
    }
}

final class ServiceHeaderInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceHeaderConfiguratorDouble
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

final class ServiceHeaderConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}

<?php

declare(strict_types=1);
use fan\core\exception\fatal;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../mock/_core/exception/RuntimeStubs.php';
require_once __DIR__ . '/../../../_core/exception/base.php';
require_once __DIR__ . '/../../../_core/exception/fatal.php';

class ExceptionFatalTest extends TestCase
{
    protected function setUp(): void
    {
        \bootstrap::$log = [];
    }

    public function testFatalExceptionUsesPublicMessageFileAndLogsOriginalMessage(): void
    {
        $previous = new \Exception('previous');
        $input = new class {
            public function serverValue(string $key, mixed $default = null): mixed
            {
                return match ($key) {
                    'HTTP_HOST' => 'example.test',
                    'REQUEST_URI' => '/broken',
                    default => $default,
                };
            }
        };
        $runtimeLogger = new class {
            public function logError(string $message): void
            {
                \bootstrap::$log[] = $message;
            }
        };
        $headerWriter = new FatalExceptionHeaderWriterDouble();

        $exception = new fatal(
            'internal details',
            'public message',
            'custom_error',
            123,
            $previous,
            $input,
            exceptionRuntimeLogger: $runtimeLogger,
            exceptionHeaderWriter: $headerWriter
        );

        $this->assertSame(['HTTP/1.1 500 Internal Server Error'], $headerWriter->headers);
        $this->assertSame('public message', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame('public message', $exception->getMessageForShow());
        $this->assertSame('custom_error', $exception->getErrorFile());
        $this->assertCount(1, \bootstrap::$log);
        $this->assertStringContainsString('Fatal error (http://example.test/broken). internal details', \bootstrap::$log[0]);
    }

    public function testFatalExceptionSourceDoesNotReadServerSuperglobalDirectly(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../_core/exception/fatal.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('$_SERVER', $source);
        $this->assertStringNotContainsString('require_once dirname(__DIR__) . \'/service/request_input.php\';', $source);
        $this->assertStringContainsString('?callable $requestInputFactory = null', $source);
        $this->assertStringContainsString('Fatal exception request input dependency is not configured.', $source);
        $this->assertStringNotContainsString('return new class', $source);
        $this->assertStringNotContainsString('headers_sent(', $source);
        $this->assertStringNotContainsString('header(', $source);
        $this->assertStringContainsString('sendInternalServerErrorHeader()', $source);
    }

    public function testFatalExceptionCanUseInjectedRequestInputFactory(): void
    {
        $runtimeLogger = new class {
            public function logError(string $message): void
            {
                \bootstrap::$log[] = $message;
            }
        };

        new fatal(
            'factory details',
            exceptionRuntimeLogger: $runtimeLogger,
            requestInputFactory: static fn(): object => new class {
                public function serverValue(string $key, mixed $default = null): mixed
                {
                    return match ($key) {
                        'HTTP_HOST' => 'factory.test',
                        'REQUEST_URI' => '/factory',
                        default => $default,
                    };
                }
            },
            exceptionHeaderWriter: new FatalExceptionHeaderWriterDouble()
        );

        $this->assertCount(1, \bootstrap::$log);
        $this->assertStringContainsString('Fatal error (http://factory.test/factory). factory details', \bootstrap::$log[0]);
    }
}

final class FatalExceptionHeaderWriterDouble
{
    public array $headers = [];

    public function sent(?string &$file = null, ?int &$line = null): bool
    {
        return false;
    }

    public function send(string $header, bool $replace = true, int $responseCode = 0): void
    {
        $this->headers[] = $header;
    }
}

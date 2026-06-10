<?php

declare(strict_types=1);

use fan\core\di\core_fatal_exception_factory;
use PHPUnit\Framework\TestCase;
use fan\core\exception\fatal;

final class CoreFatalExceptionFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreFatalExceptionWithInjectedDependencies(): void
    {
        $requestInput = new class {
            public function serverValue(string $key, mixed $default = null): mixed
            {
                return match ($key) {
                    'HTTP_HOST' => 'example.test',
                    'REQUEST_URI' => '/cache',
                    default => $default,
                };
            }
        };
        $runtimeLogger = new CoreFatalExceptionFactoryRuntimeLoggerDouble();
        $headerWriter = new CoreFatalExceptionFactoryHeaderWriterDouble();
        $previous = new RuntimeException('previous');

        $exception = (new core_fatal_exception_factory())(
            'Memcache missing.',
            code: E_USER_WARNING,
            previous: $previous,
            requestInput: $requestInput,
            exceptionRuntimeLogger: $runtimeLogger,
            exceptionHeaderWriter: $headerWriter
        );

        $this->assertInstanceOf(fatal::class, $exception);
        $this->assertSame(E_USER_WARNING, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame(['HTTP/1.1 500 Internal Server Error'], $headerWriter->headers);
        $this->assertSame([
            'Fatal error (http://example.test/cache). Memcache missing. Error at the ' . str_replace('\\', '/', $exception->getFile()) . ', line ' . $exception->getLine(),
        ], $runtimeLogger->errors);
    }}

final class CoreFatalExceptionFactoryRuntimeLoggerDouble
{
    public array $errors = [];

    public function logError(string $message): void
    {
        $this->errors[] = $message;
    }
}

final class CoreFatalExceptionFactoryHeaderWriterDouble
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

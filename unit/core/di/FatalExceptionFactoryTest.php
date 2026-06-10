<?php

declare(strict_types=1);

use fan\core\di\fatal_exception_factory;
use PHPUnit\Framework\TestCase;
use fan\project\exception\fatal;

final class FatalExceptionFactoryTest extends TestCase
{
    public function testFactoryCreatesProjectFatalException(): void
    {
        $requestInput = new class {
            public function serverValue(string $key, mixed $default = null): mixed
            {
                return match ($key) {
                    'HTTP_HOST' => 'example.test',
                    'REQUEST_URI' => '/broken',
                    default => $default,
                };
            }
        };
        $previous = new RuntimeException('previous');

        $exception = (new fatal_exception_factory())(
            'Session group is missing.',
            'Show message',
            '/tmp/error.php',
            E_USER_WARNING,
            $previous,
            $requestInput,
            exceptionRuntimeLogger: new FatalExceptionFactoryRuntimeLoggerDouble(),
            exceptionHeaderWriter: new FatalExceptionFactoryHeaderWriterDouble()
        );

        $this->assertInstanceOf(fatal::class, $exception);
        $this->assertSame(E_USER_WARNING, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }}

final class FatalExceptionFactoryRuntimeLoggerDouble
{
    public array $errors = [];

    public function logError(string $message): void
    {
        $this->errors[] = $message;
    }
}

final class FatalExceptionFactoryHeaderWriterDouble
{
    public array $headers = [];

    public function sent(?string &$file = null, ?int &$line = null): bool
    {
        return false;
    }

    public function send(string $header, bool $replace = true, int $responseCode = 0): void
    {
        $this->headers[] = [$header, $replace, $responseCode];
    }
}

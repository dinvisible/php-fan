<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../_core/exception/base.php';
require_once __DIR__ . '/../../mock/_core/exception/TestException.php';

use FanTest\_core\exception\TestException;
use PHPUnit\Framework\TestCase;


class ExceptionBaseTest extends TestCase
{
    private array $errors = [];
    private bool $capturingErrors = false;

    protected function tearDown(): void
    {
        if ($this->capturingErrors) {
            restore_error_handler();
        }
    }

    public function handleError($type, $message, $fileName = null, $lineNum = null, $errContext = null): bool
    {
        $this->errors[] = [$type, $message];
        return true;
    }

    private function captureErrors(): void
    {
        $this->errors = [];
        set_error_handler([$this, 'handleError']);
        $this->capturingErrors = true;
    }

    public function testConstructorSetsDefaultPublicMessageFileAndLogMessage(): void
    {
        $previous = new \Exception('previous');
        $exception = new TestException('log message', null, 123, $previous);

        $this->assertSame('log message', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame('log message', $exception->getMessageForLog());
        $this->assertSame('Please visit the site later.', $exception->getMessageForShow());
        $this->assertSame('error_500', $exception->getErrorFile());
        $this->assertNull($exception->getDbOper());
    }

    public function testDbOperationAllowsRollbackCommitAndNothing(): void
    {
        $this->assertSame('rollback', (new TestException('rollback', 'rollback'))->getDbOper());
        $this->assertSame('commit', (new TestException('commit', 'commit'))->getDbOper());
        $this->assertNull((new TestException('nothing', 'nothing'))->getDbOper());
        $this->assertNull((new TestException('empty', null))->getDbOper());
    }

    public function testInvalidDbOperationThrowsException(): void
    {
        $exception = new TestException('message');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorret DB-operation');

        $exception->exposeDefineDbOper('invalid');
    }

    public function testGetLogVarsFormatsScalarsNullArraysAndObjects(): void
    {
        $exception = new TestException('message');
        $logVars = $exception->getLogVars();

        $this->assertStringContainsString("'scalar' => 'visible'", $logVars);
        $this->assertStringContainsString("'null' => 'NULL'", $logVars);
        $this->assertStringContainsString("'array' => 'array[1]'", $logVars);
        $this->assertStringContainsString("'object' => 'object of \"stdClass\"'", $logVars);
        $this->assertStringNotContainsString('excludeLogVars', $logVars);
    }

    public function testInjectedExceptionDependenciesHandleDbAndLogging(): void
    {
        $databaseConnections = new class {
            public array $calls = [];

            public function fixAll(string $oper, bool $setError = true): void
            {
                $this->calls[] = [$oper, $setError];
            }
        };
        $runtimeLogger = new class {
            public array $messages = [];

            public function logError($message): void
            {
                $this->messages[] = $message;
            }
        };
        $requestService = new class {
            public function getInfoString(): string
            {
                return 'POST /di-check';
            }

            public function getAll(string $source, array $default = []): array
            {
                return $source === 'P' ? ['token' => 'abc'] : $default;
            }
        };
        $errorService = new class {
            public array $exceptionMessages = [];

            public function logExceptionMessage(string $message, string $header = '', string $note = ''): void
            {
                $this->exceptionMessages[] = [$message, $header, $note];
            }
        };

        $exception = new TestException(
            'message',
            'rollback',
            E_USER_ERROR,
            null,
            $databaseConnections,
            $runtimeLogger,
            $requestService,
            $errorService
        );

        $exception->exposeLogByPhp('php log', false);
        $exception->exposeLogByService('service log');

        $this->assertSame([['rollback', true]], $databaseConnections->calls);
        $this->assertSame(['php log'], $runtimeLogger->messages);
        $this->assertCount(1, $errorService->exceptionMessages);
        $this->assertSame('service log', $errorService->exceptionMessages[0][0]);
        $this->assertSame('Log exception', $errorService->exceptionMessages[0][1]);
        $this->assertStringContainsString('POST /di-check', $errorService->exceptionMessages[0][2]);
        $this->assertStringContainsString("'token' => 'abc'", $errorService->exceptionMessages[0][2]);
    }

    public function testInternalServerErrorHeaderUsesInjectedWriter(): void
    {
        $headerWriter = new class {
            public array $headers = [];

            public function sent(?string &$file = null, ?int &$line = null): bool
            {
                return false;
            }

            public function send(string $header, bool $replace = true, int $responseCode = 0): void
            {
                $this->headers[] = [$header, $replace, $responseCode];
            }
        };
        $exception = new TestException('message', headerWriter: $headerWriter);

        $exception->exposeSendInternalServerErrorHeader();

        $this->assertSame([['HTTP/1.1 500 Internal Server Error', true, 0]], $headerWriter->headers);
    }

    public function testSourceNoLongerFallsBackToProjectErrorSingleton(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/exception/base.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('::instance()', $source);
        $this->assertStringNotContainsString('container_registry::get()', $source);
        $this->assertStringNotContainsString('\bootstrap::', $source);
        $this->assertStringNotContainsString('error_log(', $source);
        $this->assertStringNotContainsString('new error_log_writer()', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\header_writer()', $source);
        $this->assertStringNotContainsString('new class', $source);
        $this->assertStringNotContainsString('require_once dirname(__DIR__)', $source);
        $this->assertStringContainsString('Exception runtime logger dependency is not configured.', $source);
        $this->assertStringContainsString('Exception header writer dependency is not configured.', $source);
        $this->assertStringContainsString('function sendInternalServerErrorHeader()', $source);
    }
}

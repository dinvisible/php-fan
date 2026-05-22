<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../_core/exception/base.php';
require_once __DIR__ . '/../../mock/_core/exception/TestException.php';

use FanTest\_core\exception\TestException;

class ExceptionBaseTest extends \PHPUnit\Framework\TestCase
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
}

<?php

declare(strict_types=1);

require_once __DIR__ . '/../../mock/_core/exception/RuntimeStubs.php';
require_once __DIR__ . '/../../../_core/exception/base.php';
require_once __DIR__ . '/../../../_core/exception/fatal.php';

class ExceptionFatalTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        \bootstrap::$log = [];
    }

    public function testFatalExceptionUsesPublicMessageFileAndLogsOriginalMessage(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.test';
        $_SERVER['REQUEST_URI'] = '/broken';

        $previous = new \Exception('previous');
        $exception = new \fan\core\exception\fatal('internal details', 'public message', 'custom_error', 123, $previous);

        $this->assertSame('public message', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame('public message', $exception->getMessageForShow());
        $this->assertSame('custom_error', $exception->getErrorFile());
        $this->assertCount(1, \bootstrap::$log);
        $this->assertStringContainsString('Fatal error (http://example.test/broken). internal details', \bootstrap::$log[0]);
    }
}

<?php

declare(strict_types=1);
use fan\core\exception\service\date;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../mock/_core/exception/RuntimeStubs.php';
require_once __DIR__ . '/../../../../_core/exception/base.php';
require_once __DIR__ . '/../../../../_core/exception/service/date.php';

class ExceptionServiceDateTest extends TestCase
{
    protected function setUp(): void
    {
        \bootstrap::$log = [];
    }

    public function testDateExceptionKeepsMessageCodeAndLogsThroughBootstrap(): void
    {
        $runtimeLogger = new class {
            public function logError(string $message): void
            {
                \bootstrap::$log[] = $message;
            }
        };

        $exception = new date(
            'Bad date value',
            E_USER_WARNING,
            exceptionRuntimeLogger: $runtimeLogger
        );

        $this->assertSame('Bad date value', $exception->getMessage());
        $this->assertSame(E_USER_WARNING, $exception->getCode());
        $this->assertSame('Bad date value', $exception->getMessageForLog());
        $this->assertSame('Please visit the site later.', $exception->getMessageForShow());
        $this->assertSame('error_500', $exception->getErrorFile());
        $this->assertNull($exception->getDbOper());
        $this->assertCount(1, \bootstrap::$log);
        $this->assertStringContainsString('Bad date value', \bootstrap::$log[0]);
    }
}

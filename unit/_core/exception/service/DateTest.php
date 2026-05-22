<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../mock/_core/exception/RuntimeStubs.php';
require_once __DIR__ . '/../../../../_core/exception/base.php';
require_once __DIR__ . '/../../../../_core/exception/service/date.php';

class ExceptionServiceDateTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        \bootstrap::$log = [];
    }

    public function testDateExceptionKeepsMessageCodeAndLogsThroughBootstrap(): void
    {
        $exception = new \fan\core\exception\service\date('Bad date value', E_USER_WARNING);

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

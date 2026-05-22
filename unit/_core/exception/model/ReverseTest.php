<?php

declare(strict_types=1);

use FanTest\_core\exception\TestEntity;

require_once __DIR__ . '/../../../mock/_core/exception/ExceptionDoubles.php';
require_once __DIR__ . '/../../../../_core/exception/base.php';
require_once __DIR__ . '/../../../../_core/exception/model/reverse.php';

class ExceptionModelReverseTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        \fan\project\service\database::reset();
    }

    public function testReverseExceptionKeepsEntityAndDoesNotOpenDatabaseTransactionHandling(): void
    {
        $entity = new TestEntity();
        $previous = new \Exception('previous');

        $exception = new \fan\core\exception\model\reverse($entity, 'Reverse relation failed', E_USER_WARNING, $previous);

        $this->assertSame($entity, $exception->getEntity());
        $this->assertSame('Reverse relation failed', $exception->getMessage());
        $this->assertSame(E_USER_WARNING, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertNull($exception->getDbOper());
        $this->assertSame([], \fan\project\service\database::$calls);
    }
}

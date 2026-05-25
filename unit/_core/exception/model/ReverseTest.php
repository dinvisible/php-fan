<?php

declare(strict_types=1);

use FanTest\_core\block\FakeServiceRegistry;
use FanTest\_core\exception\TestEntity;
use fan\core\exception\model\reverse;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../mock/_core/exception/ExceptionDoubles.php';
require_once __DIR__ . '/../../../../_core/exception/base.php';
require_once __DIR__ . '/../../../../_core/exception/model/reverse.php';

class ExceptionModelReverseTest extends TestCase
{
    protected function setUp(): void
    {
        FakeServiceRegistry::reset();
    }

    public function testReverseExceptionKeepsEntityAndDoesNotOpenDatabaseTransactionHandling(): void
    {
        $entity = new TestEntity();
        $previous = new \Exception('previous');

        $exception = new reverse($entity, 'Reverse relation failed', E_USER_WARNING, $previous);

        $this->assertSame($entity, $exception->getEntity());
        $this->assertSame('Reverse relation failed', $exception->getMessage());
        $this->assertSame(E_USER_WARNING, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertNull($exception->getDbOper());
        $this->assertSame([], FakeServiceRegistry::get('database_connections')->calls);
    }
}

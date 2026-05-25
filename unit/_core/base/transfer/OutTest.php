<?php

declare(strict_types=1);
use FanTest\_core\base\DatabaseConnectionsStub;
use fan\core\base\transfer\out;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../mock/_core/base/TransferStubs.php';
require_once __DIR__ . '/../../../../_core/base/transfer.php';
require_once __DIR__ . '/../../../../_core/base/transfer/out.php';

class OutTest extends TestCase
{
    protected function setUp(): void
    {
        DatabaseConnectionsStub::reset();
    }

    public function testOuterTransferCommitsByDefault(): void
    {
        $transfer = new out('https://example.test/path', 'x=1', null, new DatabaseConnectionsStub());

        $this->assertSame('out', $transfer->getTransferType());
        $this->assertSame('https://example.test/path?x=1', $transfer->getRequest());
        $this->assertTrue($transfer->isShiftCurrent());
        $this->assertSame([['commit', false]], DatabaseConnectionsStub::$calls);
    }

    public function testOuterTransferPreservesExplicitRollback(): void
    {
        new out('/path', null, 'rollback', new DatabaseConnectionsStub());

        $this->assertSame([['rollback', false]], DatabaseConnectionsStub::$calls);
    }
}

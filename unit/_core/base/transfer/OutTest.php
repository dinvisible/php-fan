<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../mock/_core/base/TransferStubs.php';
require_once __DIR__ . '/../../../../_core/base/transfer.php';
require_once __DIR__ . '/../../../../_core/base/transfer/out.php';

class OutTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        \fan\project\service\database::reset();
    }

    public function testOuterTransferCommitsByDefault(): void
    {
        $transfer = new \fan\core\base\transfer\out('https://example.test/path', 'x=1');

        $this->assertSame('out', $transfer->getTransferType());
        $this->assertSame('https://example.test/path?x=1', $transfer->getRequest());
        $this->assertTrue($transfer->isShiftCurrent());
        $this->assertSame([['commit', false]], \fan\project\service\database::$calls);
    }

    public function testOuterTransferPreservesExplicitRollback(): void
    {
        new \fan\core\base\transfer\out('/path', null, 'rollback');

        $this->assertSame([['rollback', false]], \fan\project\service\database::$calls);
    }
}

<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../mock/_core/base/TransferStubs.php';
require_once __DIR__ . '/../../../../_core/base/transfer.php';
require_once __DIR__ . '/../../../../_core/base/transfer/transfer_int.php';

class TransferIntTest extends \PHPUnit\Framework\TestCase
{
    public function testCreatesInternalTransfer(): void
    {
        $transfer = new \fan\core\base\transfer\transfer_int('/internal', 'a=1');

        $this->assertSame('int', $transfer->getTransferType());
        $this->assertSame('/internal?a=1', $transfer->getRequest());
        $this->assertTrue($transfer->isShiftCurrent());
    }
}

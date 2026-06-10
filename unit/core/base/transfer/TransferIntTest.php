<?php

declare(strict_types=1);
use fan\core\base\transfer\transfer_int;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../mock/core/base/TransferStubs.php';
require_once __DIR__ . '/../../../../core/base/transfer.php';
require_once __DIR__ . '/../../../../core/base/transfer/transfer_int.php';

class TransferIntTest extends TestCase
{
    public function testCreatesInternalTransfer(): void
    {
        $transfer = new transfer_int('/internal', 'a=1');

        $this->assertSame('int', $transfer->getTransferType());
        $this->assertSame('/internal?a=1', $transfer->getRequest());
        $this->assertTrue($transfer->isShiftCurrent());
    }
}

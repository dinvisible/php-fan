<?php

declare(strict_types=1);
use fan\core\base\transfer\sham;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../mock/core/base/TransferStubs.php';
require_once __DIR__ . '/../../../../core/base/transfer.php';
require_once __DIR__ . '/../../../../core/base/transfer/sham.php';

class ShamTest extends TestCase
{
    public function testCreatesShamTransferWithoutCurrentShift(): void
    {
        $transfer = new sham('/current', '?keep=1');

        $this->assertSame('sham', $transfer->getTransferType());
        $this->assertSame('/current?keep=1', $transfer->getRequest());
        $this->assertFalse($transfer->isShiftCurrent());
    }
}

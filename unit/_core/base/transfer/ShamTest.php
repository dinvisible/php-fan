<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../mock/_core/base/TransferStubs.php';
require_once __DIR__ . '/../../../../_core/base/transfer.php';
require_once __DIR__ . '/../../../../_core/base/transfer/sham.php';

class ShamTest extends \PHPUnit\Framework\TestCase
{
    public function testCreatesShamTransferWithoutCurrentShift(): void
    {
        $transfer = new \fan\core\base\transfer\sham('/current', '?keep=1');

        $this->assertSame('sham', $transfer->getTransferType());
        $this->assertSame('/current?keep=1', $transfer->getRequest());
        $this->assertFalse($transfer->isShiftCurrent());
    }
}

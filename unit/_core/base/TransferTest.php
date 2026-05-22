<?php

declare(strict_types=1);

require_once __DIR__ . '/../../mock/_core/base/TransferStubs.php';
require_once __DIR__ . '/../../../_core/base/transfer.php';
require_once __DIR__ . '/../../../_core/base/transfer/transfer_int.php';
require_once __DIR__ . '/../../../_core/base/transfer/sham.php';

class TransferTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        \fan\project\service\database::reset();
    }

    public function testInternalTransferStoresTypeUriQueryAndNoticeMessage(): void
    {
        $transfer = new \fan\core\base\transfer\transfer_int('/next?old=1', '?new=2');

        $this->assertSame('int', $transfer->getTransferType());
        $this->assertSame('/next?old=1', $transfer->getNewUri());
        $this->assertSame('?new=2', $transfer->getNewQueryString());
        $this->assertSame('/next?new=2', $transfer->getRequest());
        $this->assertSame('int', $transfer->getMessage());
        $this->assertSame(E_USER_NOTICE, $transfer->getCode());
        $this->assertTrue($transfer->isShiftCurrent());
    }

    public function testRequestFallsBackToUriWhenQueryStringIsEmpty(): void
    {
        $transfer = new \fan\core\base\transfer\transfer_int('/plain', '');

        $this->assertSame('/plain', $transfer->getRequest());
    }

    public function testShamTransferDoesNotShiftCurrentMatcher(): void
    {
        $transfer = new \fan\core\base\transfer\sham('/same', null);

        $this->assertSame('sham', $transfer->getTransferType());
        $this->assertFalse($transfer->isShiftCurrent());
    }

    public function testExplicitDatabaseOperationIsForwardedToDatabaseService(): void
    {
        new \fan\core\base\transfer\transfer_int('/next', null, 'rollback');

        $this->assertSame([['rollback', false]], \fan\project\service\database::$calls);
    }
}

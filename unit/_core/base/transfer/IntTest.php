<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../mock/_core/base/TransferStubs.php';
require_once __DIR__ . '/../../../../_core/base/transfer.php';
require_once __DIR__ . '/../../../../_core/base/transfer/int.php';

class IntTest extends \PHPUnit\Framework\TestCase
{
    public function testCompatibilityLoaderDefinesTransferIntClass(): void
    {
        $this->assertTrue(class_exists('\fan\core\base\transfer\transfer_int'));
        $this->assertInstanceOf(
            '\fan\core\base\transfer\transfer_int',
            new \fan\core\base\transfer\transfer_int('/internal')
        );
    }
}

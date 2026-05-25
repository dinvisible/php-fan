<?php

declare(strict_types=1);
use fan\core\base\transfer\transfer_int;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../mock/_core/base/TransferStubs.php';
require_once __DIR__ . '/../../../../_core/base/transfer.php';
require_once __DIR__ . '/../../../../_core/base/transfer/int.php';

class IntTest extends TestCase
{
    public function testCompatibilityLoaderDefinesTransferIntClass(): void
    {
        $this->assertTrue(class_exists('\fan\core\base\transfer\transfer_int'));
        $this->assertInstanceOf(
            '\fan\core\base\transfer\transfer_int',
            new transfer_int('/internal')
        );
    }
}

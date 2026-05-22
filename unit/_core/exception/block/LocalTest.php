<?php

declare(strict_types=1);

use FanTest\_core\block\TestableBaseBlock;
use FanTest\_core\block\FakeTab;

require_once __DIR__ . '/../../../mock/_core/block/GlobalFunctions.php';
require_once __DIR__ . '/../../../mock/_core/block/FrameworkStubs.php';
require_once __DIR__ . '/../../../mock/_core/block/FakeServices.php';
require_once __DIR__ . '/../../../../_core/block/base.php';
require_once __DIR__ . '/../../../mock/_core/block/TestableBaseBlock.php';
require_once __DIR__ . '/../../../../_core/exception/base.php';
require_once __DIR__ . '/../../../../_core/exception/block/local.php';

class ExceptionBlockLocalTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        \FanTest\_core\block\FakeServiceRegistry::reset();
        TestableBaseBlock::useMeta([]);
    }

    public function testLocalExceptionKeepsBlockMessageAndNoticeCode(): void
    {
        $block = new TestableBaseBlock('content', new FakeTab(), null, [], false);
        $exception = new \fan\core\exception\block\local($block, 'Local failure');

        $this->assertSame($block, $exception->getBlock());
        $this->assertSame('Local failure', $exception->getMessage());
        $this->assertSame(E_USER_NOTICE, $exception->getCode());
        $this->assertNull($exception->getDbOper());
    }
}

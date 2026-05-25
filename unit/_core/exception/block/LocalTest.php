<?php

declare(strict_types=1);

use FanTest\_core\block\TestableBaseBlock;
use FanTest\_core\block\FakeTab;
use FanTest\_core\block\FakeServiceRegistry;
use fan\core\exception\block\local;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../mock/_core/block/GlobalFunctions.php';
require_once __DIR__ . '/../../../mock/_core/block/FrameworkStubs.php';
require_once __DIR__ . '/../../../mock/_core/block/FakeServices.php';
require_once __DIR__ . '/../../../../_core/block/base.php';
require_once __DIR__ . '/../../../mock/_core/block/TestableBaseBlock.php';
require_once __DIR__ . '/../../../../_core/exception/base.php';
require_once __DIR__ . '/../../../../_core/exception/block/local.php';

class ExceptionBlockLocalTest extends TestCase
{
    protected function setUp(): void
    {
        FakeServiceRegistry::reset();
        TestableBaseBlock::useMeta([]);
    }

    public function testLocalExceptionKeepsBlockMessageAndNoticeCode(): void
    {
        $block = new TestableBaseBlock('content', new FakeTab(), null, [], false);
        $exception = new local($block, 'Local failure');

        $this->assertSame($block, $exception->getBlock());
        $this->assertSame('Local failure', $exception->getMessage());
        $this->assertSame(E_USER_NOTICE, $exception->getCode());
        $this->assertNull($exception->getDbOper());
    }
}

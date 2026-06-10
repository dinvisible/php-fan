<?php

declare(strict_types=1);

use FanTest\core\block\TestableBaseBlock;
use FanTest\core\block\FakeTab;
use FanTest\core\block\FakeServiceRegistry;
use fan\core\exception\block\local;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../mock/core/block/GlobalFunctions.php';
require_once __DIR__ . '/../../../mock/core/block/FrameworkStubs.php';
require_once __DIR__ . '/../../../mock/core/block/FakeServices.php';
require_once __DIR__ . '/../../../../core/block/base.php';
require_once __DIR__ . '/../../../mock/core/block/TestableBaseBlock.php';
require_once __DIR__ . '/../../../../core/exception/base.php';
require_once __DIR__ . '/../../../../core/exception/block/local.php';

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

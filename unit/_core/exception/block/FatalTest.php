<?php

declare(strict_types=1);

use FanTest\_core\block\FakeServiceRegistry;
use FanTest\_core\block\FakeTab;
use FanTest\_core\block\TestableBaseBlock;
use FanTest\_core\exception\FakeErrorService;
use FanTest\_core\exception\FakeRequestService;

require_once __DIR__ . '/../../../mock/_core/exception/ExceptionDoubles.php';
require_once __DIR__ . '/../../../mock/_core/block/FrameworkStubs.php';
require_once __DIR__ . '/../../../../_core/block/base.php';
require_once __DIR__ . '/../../../mock/_core/block/TestableBaseBlock.php';
require_once __DIR__ . '/../../../../_core/exception/base.php';
require_once __DIR__ . '/../../../../_core/exception/block/local.php';
require_once __DIR__ . '/../../../../_core/exception/block/fatal.php';

class ExceptionBlockFatalTest extends \PHPUnit\Framework\TestCase
{
    private FakeErrorService $errorService;

    protected function setUp(): void
    {
        FakeServiceRegistry::reset();
        TestableBaseBlock::useMeta([]);
        \fan\project\service\database::reset();

        $this->errorService = new FakeErrorService();
        FakeServiceRegistry::set('request', new FakeRequestService());
        FakeServiceRegistry::set('error', $this->errorService);
    }

    public function testBlockFatalKeepsBlockRollsBackAndLogsBlockClass(): void
    {
        $block = new TestableBaseBlock('content', new FakeTab(), null, [], false);

        $exception = new \fan\core\exception\block\fatal($block, 'Block render failed', E_USER_ERROR);

        $this->assertSame($block, $exception->getBlock());
        $this->assertSame('Block render failed', $exception->getMessage());
        $this->assertSame('rollback', $exception->getDbOper());
        $this->assertSame([['rollback', true]], \fan\project\service\database::$calls);
        $this->assertSame(
            [['Block render failed', 'Block\'s exception (CLASS: ' . get_class($block) . ').', 'GET /unit-test']],
            $this->errorService->exceptionMessages
        );
    }
}

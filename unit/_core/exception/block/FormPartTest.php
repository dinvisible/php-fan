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
require_once __DIR__ . '/../../../../_core/exception/block/form_part.php';

class ExceptionBlockFormPartTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        \FanTest\_core\block\FakeServiceRegistry::reset();
        TestableBaseBlock::useMeta([]);
    }

    public function testFormPartExceptionExposesBlockNameAndValidationMessages(): void
    {
        $block = new TestableBaseBlock('formBlock', new FakeTab(), null, [], false);
        $messages = ['email' => 'Invalid email', 'name' => 'Required'];

        $exception = new \fan\core\exception\block\form_part($block, $messages);

        $this->assertSame($block, $exception->getBlock());
        $this->assertSame('formBlock', $exception->getBlockName());
        $this->assertSame($messages, $exception->getErrorMessages());
        $this->assertSame('Form part error', $exception->getMessage());
        $this->assertSame(E_USER_WARNING, $exception->getCode());
        $this->assertNull($exception->getDbOper());
    }
}

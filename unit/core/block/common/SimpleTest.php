<?php

declare(strict_types=1);

use fan\core\block\common\simple;
use FanTest\core\SourceFileContractTestCase;
use fan\core\block\base;


class BlockCommonSimpleTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/block/common/simple.php';

    public function testSimpleBlockIsThinBaseBlockSubclass(): void
    {
        $block = new BlockCommonSimpleProbe();

        $this->assertInstanceOf(base::class, $block);
    }
}

final class BlockCommonSimpleProbe extends simple
{
    public function __construct()
    {
    }
}

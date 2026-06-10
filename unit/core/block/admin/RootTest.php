<?php

declare(strict_types=1);

use fan\core\block\admin\root;
use fan\core\block\root\html;
use FanTest\core\SourceFileContractTestCase;

class BlockAdminRootTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/block/admin/root.php';

    public function testRootBlockExtendsHtmlRootBlock(): void
    {
        $reflection = new ReflectionClass(root::class);

        $this->assertTrue($reflection->isSubclassOf(html::class));
    }
}

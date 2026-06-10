<?php

declare(strict_types=1);

use fan\core\base\meta\maker_state;
use FanTest\core\SourceFileContractTestCase;

final class MetaMakerStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/base/meta/maker_state.php';

    public function testStoresBlockMetaSourcesByClassName(): void
    {
        $state = new maker_state();

        $this->assertFalse($state->hasBlockSource('Block'));
        $this->assertSame([], $state->getBlockSource('Block'));

        $state->setBlockSource('Block', ['own' => ['title' => 'Page']]);

        $this->assertTrue($state->hasBlockSource('Block'));
        $this->assertSame(['own' => ['title' => 'Page']], $state->getBlockSource('Block'));
        $this->assertSame([
            'Block' => ['own' => ['title' => 'Page']],
        ], $state->blockSources());
    }

    public function testClearRemovesCachedSources(): void
    {
        $state = new maker_state();
        $state->setBlockSource('Block', ['own' => true]);

        $state->clear();

        $this->assertFalse($state->hasBlockSource('Block'));
        $this->assertSame([], $state->blockSources());
    }
}

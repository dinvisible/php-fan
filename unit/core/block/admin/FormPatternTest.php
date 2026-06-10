<?php

declare(strict_types=1);

use fan\core\block\admin\form_pattern;
use FanTest\core\SourceFileContractTestCase;

class BlockAdminFormPatternTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/block/admin/form_pattern.php';

    public function testInitWritesOkTextResponse(): void
    {
        $block = new BlockAdminFormPatternProbe();

        $block->init();

        $this->assertSame(['ok'], $block->textCalls);
    }
}

final class BlockAdminFormPatternProbe extends form_pattern
{
    public array $textCalls = [];

    public function __construct()
    {
    }

    public function setText(string $text, bool $merge = true): static
    {
        $this->textCalls[] = $text;

        return $this;
    }
}

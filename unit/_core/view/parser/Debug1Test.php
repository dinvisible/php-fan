<?php

declare(strict_types=1);

use FanTest\_core\SourceFileContractTestCase;

class GeneratedPendingViewParserDebug1Test extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/view/parser/debug1.php';

    public function testDebugExternalFilesUseBooleanMode(): void
    {
        $this->assertStringContainsString('$this->debug->setExtFiles($rootBlock, true);', $this->sourceCode());
    }
}

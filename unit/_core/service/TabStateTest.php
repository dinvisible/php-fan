<?php

declare(strict_types=1);

use fan\core\service\tab_state;
use FanTest\_core\SourceFileContractTestCase;

class ServiceTabStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/tab_state.php';

    public function testTracksErrorTransferCodesInOrder(): void
    {
        $state = new tab_state();

        $this->assertTrue($state->isErrorTransferEmpty());
        $this->assertNull($state->lastErrorTransferCode());

        $state->pushErrorTransferCode(404);
        $state->pushErrorTransferCode(500);

        $this->assertFalse($state->isErrorTransferEmpty());
        $this->assertTrue($state->hasErrorTransferCode(404));
        $this->assertTrue($state->hasErrorTransferCode(500));
        $this->assertFalse($state->hasErrorTransferCode(403));
        $this->assertSame(500, $state->lastErrorTransferCode());
    }

    public function testClearRemovesErrorTransferCodes(): void
    {
        $state = new tab_state();
        $state->pushErrorTransferCode(404);

        $state->clear();

        $this->assertTrue($state->isErrorTransferEmpty());
        $this->assertNull($state->lastErrorTransferCode());
    }
}

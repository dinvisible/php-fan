<?php

declare(strict_types=1);

use fan\core\service\pager_state;
use FanTest\_core\SourceFileContractTestCase;

class ServicePagerStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/pager_state.php';

    public function testStoresPagerInstancesByBlockName(): void
    {
        $state = new pager_state();
        $pager = new stdClass();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance('items'));

        $state->setInstance('items', $pager);

        $this->assertTrue($state->hasInstances());
        $this->assertSame($pager, $state->getInstance('items'));
    }

    public function testClearRemovesStoredInstances(): void
    {
        $state = new pager_state();
        $state->setInstance('items', new stdClass());

        $state->clear();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance('items'));
    }
}

<?php

declare(strict_types=1);

use fan\core\service\date_state;
use FanTest\_core\SourceFileContractTestCase;

class ServiceDateStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/date_state.php';

    public function testStoresGlobalConfig(): void
    {
        $state = new date_state();
        $config = new stdClass();

        $this->assertNull($state->getGlobalConfig());

        $state->setGlobalConfig($config);

        $this->assertSame($config, $state->getGlobalConfig());
    }

    public function testStoresDateInstancesByFullDateKey(): void
    {
        $state = new date_state();
        $date = new stdClass();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance(true, 'UTC', 'mysql', '20260526123456000000'));

        $state->setInstance(true, 'UTC', 'mysql', '20260526123456000000', $date);

        $this->assertTrue($state->hasInstances());
        $this->assertSame($date, $state->getInstance(true, 'UTC', 'mysql', '20260526123456000000'));
        $this->assertNull($state->getInstance(false, 'UTC', 'mysql', '20260526123456000000'));
    }

    public function testClearRemovesConfigAndInstances(): void
    {
        $state = new date_state();
        $state->setGlobalConfig(new stdClass());
        $state->setInstance(true, 'UTC', 'mysql', '20260526123456000000', new stdClass());

        $state->clear();

        $this->assertNull($state->getGlobalConfig());
        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance(true, 'UTC', 'mysql', '20260526123456000000'));
    }
}

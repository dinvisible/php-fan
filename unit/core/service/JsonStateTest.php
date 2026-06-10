<?php

declare(strict_types=1);

use fan\core\service\json_state;
use FanTest\core\SourceFileContractTestCase;

class ServiceJsonStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/json_state.php';

    public function testStoresJsonInstancesByBase64Mode(): void
    {
        $state = new json_state();
        $plain = new stdClass();
        $base64 = new stdClass();

        $this->assertNull($state->getInstance(false));
        $this->assertNull($state->getInstance(true));

        $state->setInstance(false, $plain);
        $state->setInstance(true, $base64);

        $this->assertSame($plain, $state->getInstance(false));
        $this->assertSame($base64, $state->getInstance(true));
    }

    public function testClearRemovesStoredInstances(): void
    {
        $state = new json_state();
        $state->setInstance(false, new stdClass());
        $state->setInstance(true, new stdClass());

        $state->clear();

        $this->assertNull($state->getInstance(false));
        $this->assertNull($state->getInstance(true));
    }
}

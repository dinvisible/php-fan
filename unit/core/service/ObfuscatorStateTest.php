<?php

declare(strict_types=1);

use fan\core\service\obfuscator_state;
use FanTest\core\SourceFileContractTestCase;

class ServiceObfuscatorStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/obfuscator_state.php';

    public function testStoresObfuscatorInstancesByType(): void
    {
        $state = new obfuscator_state();
        $obfuscator = new stdClass();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance('css'));

        $state->setInstance('css', $obfuscator);

        $this->assertTrue($state->hasInstances());
        $this->assertSame($obfuscator, $state->getInstance('css'));
    }

    public function testClearRemovesStoredInstances(): void
    {
        $state = new obfuscator_state();
        $state->setInstance('css', new stdClass());

        $state->clear();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance('css'));
    }
}

<?php

declare(strict_types=1);

use fan\core\service\rest_state;
use FanTest\core\SourceFileContractTestCase;

class ServiceRestStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/rest_state.php';

    public function testResolvesAndKeepsFirstConfiguredDefaultConnection(): void
    {
        $state = new rest_state();

        $this->assertSame('main', $state->resolveConnectionName(null, 'main'));
        $this->assertSame('main', $state->getDefaultName());
        $this->assertSame('main', $state->resolveConnectionName('', 'secondary'));
        $this->assertSame('custom', $state->resolveConnectionName('custom', 'secondary'));
    }

    public function testStoresRestInstancesByConnectionName(): void
    {
        $state = new rest_state();
        $rest = new stdClass();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance('main'));

        $state->setInstance('main', $rest);

        $this->assertTrue($state->hasInstances());
        $this->assertSame($rest, $state->getInstance('main'));
    }

    public function testClearRemovesInstancesAndDefaultName(): void
    {
        $state = new rest_state();
        $state->resolveConnectionName(null, 'main');
        $state->setInstance('main', new stdClass());

        $state->clear();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getDefaultName());
        $this->assertNull($state->getInstance('main'));
    }
}

<?php

declare(strict_types=1);

use fan\core\service\service_single_state;
use FanTest\core\SourceFileContractTestCase;

final class ServiceSingleStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/service_single_state.php';

    public function testStoresSingletonInstancesByClassName(): void
    {
        $state = new service_single_state();
        $service = new stdClass();

        $this->assertFalse($state->hasInstance('fan\project\service\application'));
        $this->assertNull($state->getInstance('fan\project\service\application'));

        $state->setInstance('fan\project\service\application', $service);

        $this->assertTrue($state->hasInstance('fan\project\service\application'));
        $this->assertSame($service, $state->getInstance('fan\project\service\application'));
        $this->assertSame([
            'fan\project\service\application' => $service,
        ], $state->instances());
    }

    public function testClearRemovesStoredInstances(): void
    {
        $state = new service_single_state();
        $state->setInstance('fan\project\service\application', new stdClass());

        $state->clear();

        $this->assertFalse($state->hasInstance('fan\project\service\application'));
        $this->assertSame([], $state->instances());
    }
}

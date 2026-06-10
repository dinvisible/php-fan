<?php

declare(strict_types=1);

use fan\core\service\service_listener_state;
use FanTest\core\SourceFileContractTestCase;

final class ServiceListenerStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/service_listener_state.php';

    public function testListenersAreGroupedByServiceAndEvent(): void
    {
        $state = new service_listener_state();
        $first = static function (): void {
        };
        $second = static function (): void {
        };

        $state->subscribe('user', 'changed', $first);
        $state->subscribe('user', 'changed', $second);
        $state->subscribe('role', 'changed', $first);

        $this->assertSame([$first, $second], $state->listenersFor('user', 'changed'));
        $this->assertSame([$first], $state->listenersFor('role', 'changed'));
        $this->assertSame([], $state->listenersFor('missing', 'changed'));
    }

    public function testListenersSnapshotAndClear(): void
    {
        $state = new service_listener_state();
        $listener = static function (): void {
        };

        $state->subscribe('application', 'setAppName', $listener);

        $this->assertSame([
            'application' => [
                'setAppName' => [$listener],
            ],
        ], $state->listeners());

        $state->clear();

        $this->assertSame([], $state->listeners());
    }
}

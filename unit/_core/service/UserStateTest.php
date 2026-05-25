<?php

declare(strict_types=1);

use fan\core\service\user_state;
use FanTest\_core\SourceFileContractTestCase;

class ServiceUserStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/user_state.php';

    public function testStoresInstancesBySpaceAndIdentifier(): void
    {
        $state = new user_state();
        $user = new stdClass();

        $this->assertNull($state->getInstance('main', 'tester'));

        $state->setInstance('main', 'tester', $user);

        $this->assertSame($user, $state->getInstance('main', 'tester'));
    }

    public function testStoresCurrentUsersPrioritySpaceAndCurrentUserSpace(): void
    {
        $state = new user_state();
        $current = ['main' => new stdClass()];
        $priority = ['crm' => 'main'];

        $this->assertNull($state->getCurrentUsers());
        $this->assertNull($state->getPrioritySpace());
        $this->assertNull($state->getCurrentUserSpace());

        $state->setCurrentUsers($current);
        $state->setPrioritySpace($priority);
        $state->setCurrentUserSpace('main');

        $this->assertSame($current, $state->getCurrentUsers());
        $this->assertSame($priority, $state->getPrioritySpace());
        $this->assertSame('main', $state->getCurrentUserSpace());
    }

    public function testStoresSessionBridge(): void
    {
        $state = new user_state();
        $session = new stdClass();

        $this->assertNull($state->getSession());

        $state->setSession($session);

        $this->assertSame($session, $state->getSession());
    }

    public function testClearRemovesStoredState(): void
    {
        $state = new user_state();
        $state->setInstance('main', 'tester', new stdClass());
        $state->setCurrentUsers(['main' => new stdClass()]);
        $state->setPrioritySpace(['crm' => 'main']);
        $state->setCurrentUserSpace('main');
        $state->setSession(new stdClass());

        $state->clear();

        $this->assertNull($state->getInstance('main', 'tester'));
        $this->assertNull($state->getCurrentUsers());
        $this->assertNull($state->getPrioritySpace());
        $this->assertNull($state->getCurrentUserSpace());
        $this->assertNull($state->getSession());
    }
}

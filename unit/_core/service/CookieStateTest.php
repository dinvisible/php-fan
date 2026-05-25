<?php

declare(strict_types=1);

use fan\core\service\cookie_state;
use FanTest\_core\SourceFileContractTestCase;

class ServiceCookieStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/cookie_state.php';

    public function testStoresCookieInstancesByLegacyPathAndDomainKeys(): void
    {
        $state = new cookie_state();
        $cookie = new stdClass();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance('/app', 'example.test'));

        $state->setInstance('/app', 'example.test', $cookie);

        $this->assertTrue($state->hasInstances());
        $this->assertSame($cookie, $state->getInstance('/app', 'example.test'));
    }

    public function testInitializesCookieDataOnlyOnceAndAllowsMutations(): void
    {
        $state = new cookie_state();

        $state->initializeData(['prefs' => 'dark']);
        $state->initializeData(['prefs' => 'light']);

        $this->assertTrue($state->hasData('prefs'));
        $this->assertSame('dark', $state->getData('prefs'));
        $this->assertSame(['prefs' => 'dark'], $state->getAllData());

        $state->setData('mode', 'compact');
        $state->deleteData('prefs');

        $this->assertFalse($state->hasData('prefs'));
        $this->assertSame(['mode' => 'compact'], $state->getAllData());
    }

    public function testClearRemovesInstancesAndCookieData(): void
    {
        $state = new cookie_state();
        $state->setInstance('/app', 'example.test', new stdClass());
        $state->initializeData(['prefs' => 'dark']);

        $state->clear();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance('/app', 'example.test'));
        $this->assertSame([], $state->getAllData());
    }
}

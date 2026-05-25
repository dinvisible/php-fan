<?php

declare(strict_types=1);

use fan\core\service\curl_state;
use FanTest\_core\SourceFileContractTestCase;

class ServiceCurlStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/curl_state.php';

    public function testStoresCurlInstancesByIndexAndUrl(): void
    {
        $state = new curl_state();
        $primary = new stdClass();
        $secondary = new stdClass();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance(0, 'https://example.test'));

        $state->setInstance(0, 'https://example.test', $primary);
        $state->setInstance('alternate', 'https://example.test', $secondary);

        $this->assertTrue($state->hasInstances());
        $this->assertSame($primary, $state->getInstance(0, 'https://example.test'));
        $this->assertSame($secondary, $state->getInstance('alternate', 'https://example.test'));
    }

    public function testRemoveInstanceOnlyRemovesMatchingIndexAndUrl(): void
    {
        $state = new curl_state();
        $primary = new stdClass();
        $secondary = new stdClass();
        $state->setInstance(0, 'https://example.test', $primary);
        $state->setInstance(1, 'https://example.test', $secondary);

        $state->removeInstance(0, 'https://example.test');

        $this->assertNull($state->getInstance(0, 'https://example.test'));
        $this->assertSame($secondary, $state->getInstance(1, 'https://example.test'));
        $this->assertTrue($state->hasInstances());
    }

    public function testClearRemovesStoredInstances(): void
    {
        $state = new curl_state();
        $state->setInstance(0, 'https://example.test', new stdClass());

        $state->clear();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance(0, 'https://example.test'));
    }
}

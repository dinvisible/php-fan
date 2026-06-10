<?php

declare(strict_types=1);

use fan\core\service\cache_state;
use FanTest\core\SourceFileContractTestCase;

class ServiceCacheStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/cache_state.php';

    public function testStoresCacheInstancesByType(): void
    {
        $state = new cache_state();
        $cache = new stdClass();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance('common_by_file'));

        $state->setInstance('common_by_file', $cache);

        $this->assertTrue($state->hasInstances());
        $this->assertSame($cache, $state->getInstance('common_by_file'));
    }

    public function testClearRemovesStoredInstances(): void
    {
        $state = new cache_state();
        $state->setInstance('common_by_file', new stdClass());

        $state->clear();

        $this->assertFalse($state->hasInstances());
        $this->assertNull($state->getInstance('common_by_file'));
    }
}

<?php

declare(strict_types=1);

use fan\core\service\cache_memcache_state;
use FanTest\core\SourceFileContractTestCase;

class ServiceCacheMemcacheStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/cache_memcache_state.php';

    public function testStoresKeepersByCacheType(): void
    {
        $state = new cache_memcache_state();
        $keeper = new stdClass();

        $this->assertNull($state->getKeeper('page'));

        $state->setKeeper('page', $keeper);

        $this->assertSame($keeper, $state->getKeeper('page'));
        $this->assertNull($state->getKeeper('config'));
    }

    public function testClearRemovesStoredKeepers(): void
    {
        $state = new cache_memcache_state();
        $state->setKeeper('page', new stdClass());

        $state->clear();

        $this->assertNull($state->getKeeper('page'));
    }
}

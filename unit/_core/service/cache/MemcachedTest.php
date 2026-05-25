<?php

declare(strict_types=1);

use fan\core\service\cache;
use fan\core\service\cache\memcached;
use FanTest\_core\SourceFileContractTestCase;

class ServiceCacheMemcachedTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/cache/memcached.php';

    public function testGetReturnsDefaultBecauseEngineDoesNotLoadData(): void
    {
        $engine = $this->engine();

        $this->assertSame('fallback', $engine->get('fallback'));
        $this->assertFalse($engine->isLoaded());
    }

    public function testSaveOverrideMarksAutoSavedDataAsSaved(): void
    {
        $engine = $this->engine();

        $this->assertSame($engine, $engine->set(['cached' => true], true));

        $this->assertTrue($engine->isLoaded());
        $this->assertTrue($engine->isSaved());
        $this->assertSame(['cached' => true], $engine->get());
    }

    public function testDeleteUsesBaseDeletionToClearDataAndMeta(): void
    {
        $engine = $this->engine();
        $engine->set('payload', false);

        $this->assertSame($engine, $engine->delete());

        $this->assertSame('fallback', $engine->get('fallback'));
        $this->assertSame([], $engine->getMeta(false));
    }

    private function engine(): memcached
    {
        return new memcached(new ServiceCacheMemcachedFacadeDouble(), 'page', 'home', ['LIFETIME' => 30]);
    }
}

final class ServiceCacheMemcachedFacadeDouble extends cache
{
    public function __construct()
    {
    }
}

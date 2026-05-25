<?php

declare(strict_types=1);

use fan\core\service\config_state;
use FanTest\_core\SourceFileContractTestCase;

class ServiceConfigStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/config_state.php';

    public function testStoresConfigInstancesAndEngines(): void
    {
        $state = new config_state();
        $config = new stdClass();
        $engine = new stdClass();

        $this->assertNull($state->getInstance('service'));
        $this->assertNull($state->getEngine('arr'));

        $state->setInstance('service', $config);
        $state->setEngine('arr', $engine);

        $this->assertSame($config, $state->getInstance('service'));
        $this->assertSame($engine, $state->getEngine('arr'));
    }

    public function testStoresCacheThisConfigAndApplicationDependentFiles(): void
    {
        $state = new config_state();
        $cache = new stdClass();
        $thisConfig = new stdClass();

        $state->setCache($cache);
        $state->setThisConfig($thisConfig);
        $state->setAppDepended([
            'route' => '{APP_NAME}/route',
        ]);

        $this->assertSame($cache, $state->getCache());
        $this->assertSame($thisConfig, $state->getThisConfig());
        $this->assertSame(['route' => '{APP_NAME}/route'], $state->getAppDepended());
    }

    public function testClearRemovesAllStoredState(): void
    {
        $state = new config_state();
        $state->setInstance('service', new stdClass());
        $state->setEngine('arr', new stdClass());
        $state->setCache(new stdClass());
        $state->setThisConfig(new stdClass());
        $state->setAppDepended(['route' => '{APP_NAME}/route']);

        $state->clear();

        $this->assertNull($state->getInstance('service'));
        $this->assertNull($state->getEngine('arr'));
        $this->assertNull($state->getCache());
        $this->assertNull($state->getThisConfig());
        $this->assertSame([], $state->getAppDepended());
    }
}

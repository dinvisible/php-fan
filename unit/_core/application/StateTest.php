<?php

declare(strict_types=1);

use fan\core\bootstrap\state;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;

final class BootstrapStateTest extends TestCase
{
    public function testStateStoresBootstrapLifecycleValues(): void
    {
        $state = new state();
        $initializer = new stdClass();
        $loader = new stdClass();
        $runner = new stdClass();
        $container = new container();

        $state->markInitialized();
        $state->setCli(true);
        $state->setConfig(['config_cache' => ['enabled' => true]]);
        $state->setReplacement(['{ROOT}' => '/tmp/app']);
        $state->setLogDir('/tmp/logs');
        $state->setInitializer($initializer);
        $state->setLoader($loader);
        $state->setRunner($runner);
        $state->setContainer($container);

        $this->assertTrue($state->isInit());
        $this->assertTrue($state->isCli());
        $this->assertSame(['enabled' => true], $state->configValue('config_cache'));
        $this->assertSame('/tmp/app/cache', $state->fillPlaceholder('{ROOT}/cache'));
        $this->assertSame('/tmp/logs', $state->logDir());
        $this->assertSame($initializer, $state->initializer());
        $this->assertSame($loader, $state->loader());
        $this->assertSame($runner, $state->runner());
        $this->assertSame($container, $state->container());
    }

    public function testStateCreatesStablePidLazily(): void
    {
        $state = new state();

        $pid = $state->pid();

        $this->assertNotSame('', $pid);
        $this->assertSame($pid, $state->pid());
    }
}

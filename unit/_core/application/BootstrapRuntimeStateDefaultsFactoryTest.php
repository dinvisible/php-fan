<?php

declare(strict_types=1);

use fan\core\di\bootstrap_runtime_state_defaults_factory;
use PHPUnit\Framework\TestCase;
use fan\core\base\meta\maker_state;
use fan\core\base\model\spec_file\image\row_state;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\core\view\router\loader_state;


final class BootstrapRuntimeStateDefaultsFactoryTest extends TestCase
{
    public function testFactoryCreatesRuntimeStateDefaults(): void
    {
        $factory = new bootstrap_runtime_state_defaults_factory(
            static fn(): callable => static fn(): array => [
                'serviceListenerState' => new service_listener_state(),
                'serviceSingleState' => new service_single_state(),
                'viewLoaderState' => new loader_state(),
                'metaMakerState' => new maker_state(),
                'specFileImageRowState' => new row_state(),
            ]
        );
        $runtimeStateFactory = $factory->runtimeStateFactory();
        $state = $runtimeStateFactory();

        $this->assertIsCallable($runtimeStateFactory);
        $this->assertInstanceOf(service_listener_state::class, $state['serviceListenerState']);
        $this->assertInstanceOf(service_single_state::class, $state['serviceSingleState']);
        $this->assertInstanceOf(loader_state::class, $state['viewLoaderState']);
        $this->assertInstanceOf(maker_state::class, $state['metaMakerState']);
        $this->assertInstanceOf(row_state::class, $state['specFileImageRowState']);
    }

    public function testSourceOwnsBootstrapRuntimeStateDefaultsBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/bootstrap_runtime_state_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_runtime_state_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $runtimeStateFactory;', $source);
        $this->assertStringContainsString('public function __construct(callable $runtimeStateFactory)', $source);
        $this->assertStringContainsString('$this->runtimeStateFactory = \Closure::fromCallable($runtimeStateFactory);', $source);
        $this->assertStringContainsString('public function runtimeStateFactory(): callable', $source);
        $this->assertStringContainsString('return ($this->runtimeStateFactory)();', $source);
        $this->assertStringNotContainsString('public static function runtimeStateFactory(): callable', $source);
        $this->assertStringNotContainsString('runtimeStateClasses', $source);
        $this->assertStringNotContainsString('defaultStateClasses', $source);
        $this->assertStringNotContainsString('loadClass', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString('new \fan\core\service\service_listener_state()', $source);
        $this->assertStringNotContainsString('new \fan\core\service\service_single_state()', $source);
        $this->assertStringNotContainsString('new \fan\core\view\router\loader_state()', $source);
        $this->assertStringNotContainsString('new \fan\core\base\meta\maker_state()', $source);
        $this->assertStringNotContainsString('new \fan\core\base\model\spec_file\image\row_state()', $source);
    }
}

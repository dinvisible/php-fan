<?php

declare(strict_types=1);

use fan\core\di\bootstrap_runtime_state_factory_defaults_provider_factory;
use PHPUnit\Framework\TestCase;
use fan\core\base\meta\maker_state;
use fan\core\base\model\spec_file\image\row_state;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\core\view\router\loader_state;


final class BootstrapRuntimeStateFactoryDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapRuntimeStateFactoryDefault(): void
    {
        $factory = (new bootstrap_runtime_state_factory_defaults_provider_factory())();
        $state = $factory();

        $this->assertIsCallable($factory);
        $this->assertInstanceOf(service_listener_state::class, $state['serviceListenerState']);
        $this->assertInstanceOf(service_single_state::class, $state['serviceSingleState']);
        $this->assertInstanceOf(loader_state::class, $state['viewLoaderState']);
        $this->assertInstanceOf(maker_state::class, $state['metaMakerState']);
        $this->assertInstanceOf(row_state::class, $state['specFileImageRowState']);
    }

    public function testSourceOwnsBootstrapRuntimeStateFactoryDefaultsCompositionBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/bootstrap_runtime_state_factory_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_runtime_state_factory_defaults_provider_factory', $source);
        $this->assertStringContainsString('public function __invoke(): callable', $source);
        $this->assertStringContainsString('return (new bootstrap_runtime_state_defaults_provider_factory())()->runtimeStateFactory();', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_runtime_state_defaults_provider_factory.php';", $source);
        $this->assertStringNotContainsString('new bootstrap_runtime_defaults_factory(', $source);
        $this->assertStringNotContainsString('new bootstrap_runtime_factory(', $source);
        $this->assertStringNotContainsString('new bootstrap_runtime_service_defaults_provider_factory()', $source);
        $this->assertStringNotContainsString('new \fan\core\service\service_listener_state()', $source);
        $this->assertStringNotContainsString('new \fan\core\service\service_single_state()', $source);
    }
}

<?php

declare(strict_types=1);

use fan\core\di\bootstrap_runtime_service_factory_defaults_provider_factory;
use PHPUnit\Framework\TestCase;
use fan\core\service\bootstrap_runtime;


final class BootstrapRuntimeServiceFactoryDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapRuntimeServiceFactoryDefault(): void
    {
        $factory = (new bootstrap_runtime_service_factory_defaults_provider_factory())();

        $this->assertIsCallable($factory);
        $this->assertInstanceOf(bootstrap_runtime::class, $factory());
    }

    public function testSourceOwnsBootstrapRuntimeServiceFactoryDefaultsCompositionBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/bootstrap_runtime_service_factory_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_runtime_service_factory_defaults_provider_factory', $source);
        $this->assertStringContainsString('public function __invoke(): callable', $source);
        $this->assertStringContainsString('return (new bootstrap_runtime_service_defaults_provider_factory())()->runtimeServiceFactory();', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_runtime_service_defaults_provider_factory.php';", $source);
        $this->assertStringNotContainsString('new bootstrap_runtime_defaults_factory(', $source);
        $this->assertStringNotContainsString('new bootstrap_runtime_factory(', $source);
        $this->assertStringNotContainsString('new bootstrap_runtime_state_defaults_provider_factory()', $source);
        $this->assertStringNotContainsString('new \fan\core\service\bootstrap_runtime(', $source);
    }
}

<?php

declare(strict_types=1);

use fan\core\di\application_container_registry_defaults_provider_factory;
use fan\core\di\application_registry_defaults_provider;
use PHPUnit\Framework\TestCase;

final class ApplicationContainerRegistryDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationContainerRegistryDefaultFactory(): void
    {
        $factory = (new application_container_registry_defaults_provider_factory())();

        $this->assertIsCallable($factory);
        $this->assertInstanceOf(application_registry_defaults_provider::class, $factory());
    }

    public function testFactoryUsesInjectedRegistryDefaultsProviderFactory(): void
    {
        $defaultFactory = (new application_container_registry_defaults_provider_factory())();
        $expected = $defaultFactory();
        $factory = (new application_container_registry_defaults_provider_factory(
            static fn(): application_registry_defaults_provider => $expected
        ))();

        $this->assertSame($expected, $factory());
    }

    public function testFactoryUsesInjectedRegistryDefaultsProviderFactoryProvider(): void
    {
        $expected = (new application_container_registry_defaults_provider_factory())()();
        $calls = 0;
        $factory = (new application_container_registry_defaults_provider_factory(
            registryDefaultsProviderFactoryProvider: static function () use (&$calls, $expected): callable {
                return static function () use (&$calls, $expected): application_registry_defaults_provider {
                    $calls++;

                    return $expected;
                };
            }
        ))();

        $this->assertSame($expected, $factory());
        $this->assertSame(1, $calls);
    }

    public function testSourceOwnsApplicationContainerRegistryDefaultsCompositionBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_container_registry_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class application_container_registry_defaults_provider_factory', $source);
        $this->assertStringContainsString('private \Closure $registryDefaultsProviderFactoryProvider;', $source);
        $this->assertStringContainsString('public function __construct(', $source);
        $this->assertStringContainsString('?callable $registryDefaultsProviderFactoryProvider = null', $source);
        $this->assertStringContainsString('$this->registryDefaultsProviderFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('? static function () use ($registryDefaultsProviderFactory): callable {', $source);
        $this->assertStringContainsString('return $registryDefaultsProviderFactory;', $source);
        $this->assertStringContainsString('public function __invoke(): callable', $source);
        $this->assertStringContainsString('$registryDefaultsProviderFactory = ($this->registryDefaultsProviderFactoryProvider)();', $source);
        $this->assertStringContainsString('return static function () use ($registryDefaultsProviderFactory): application_registry_defaults_provider {', $source);
        $this->assertStringContainsString('return $registryDefaultsProviderFactory();', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../factory/application_registry_defaults_provider_factory.php';", $source);
        $this->assertStringContainsString('?? static function (): callable {', $source);
        $this->assertStringContainsString('return new application_registry_defaults_provider_factory();', $source);
        $this->assertStringNotContainsString('private ?\Closure $registryDefaultsProviderFactory', $source);
        $this->assertStringNotContainsString('private function registryDefaultsProviderFactory(', $source);
        $this->assertStringNotContainsString('private function registryDefaultsProviderFactoryProvider(): callable', $source);
        $this->assertStringNotContainsString('(new \fan\core\di\application_registry_defaults_provider_factory())();', $source);
        $this->assertStringNotContainsString('new application_container_defaults_factory(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_container_dependency_provider(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_factory_provider_defaults_provider_factory(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_service_registrar_defaults_provider_factory(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_service_creator_defaults_provider_factory(', $source);
    }
}

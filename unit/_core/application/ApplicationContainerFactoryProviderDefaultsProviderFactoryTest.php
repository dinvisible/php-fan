<?php

declare(strict_types=1);

use fan\core\di\application_container_factory_provider_defaults_provider_factory;
use fan\core\di\application_factory_provider_defaults_provider;
use PHPUnit\Framework\TestCase;
use fan\core\di\application_adapter_registry;
use fan\core\di\application_registry_defaults_provider_factory;


final class ApplicationContainerFactoryProviderDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationContainerFactoryProviderDefaultFactory(): void
    {
        $factory = (new application_container_factory_provider_defaults_provider_factory())();
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();

        $this->assertIsCallable($factory);
        $this->assertInstanceOf(application_factory_provider_defaults_provider::class, $factory($adapterRegistry));
    }

    public function testFactoryUsesInjectedFactoryProviderDefaultsProviderFactory(): void
    {
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();
        $defaultFactory = (new application_container_factory_provider_defaults_provider_factory())();
        $expected = $defaultFactory($adapterRegistry);
        $factory = (new application_container_factory_provider_defaults_provider_factory(
            static fn(application_adapter_registry $receivedRegistry): application_factory_provider_defaults_provider => $receivedRegistry === $adapterRegistry
                ? $expected
                : throw new RuntimeException('Unexpected adapter registry.')
        ))();

        $this->assertSame($expected, $factory($adapterRegistry));
    }

    public function testFactoryUsesInjectedFactoryProviderDefaultsProviderFactoryProvider(): void
    {
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();
        $expected = (new application_container_factory_provider_defaults_provider_factory())()($adapterRegistry);
        $receivedRegistry = null;
        $factory = (new application_container_factory_provider_defaults_provider_factory(
            factoryProviderDefaultsProviderFactoryProvider: static function () use (&$receivedRegistry, $expected): callable {
                return static function (
                    application_adapter_registry $registry
                ) use (&$receivedRegistry, $expected): application_factory_provider_defaults_provider {
                    $receivedRegistry = $registry;

                    return $expected;
                };
            }
        ))();

        $this->assertSame($expected, $factory($adapterRegistry));
        $this->assertSame($adapterRegistry, $receivedRegistry);
    }

    public function testSourceOwnsApplicationContainerFactoryProviderDefaultsCompositionBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_container_factory_provider_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class application_container_factory_provider_defaults_provider_factory', $source);
        $this->assertStringContainsString('private \Closure $factoryProviderDefaultsProviderFactoryProvider;', $source);
        $this->assertStringContainsString('public function __construct(', $source);
        $this->assertStringContainsString('?callable $factoryProviderDefaultsProviderFactoryProvider = null', $source);
        $this->assertStringContainsString('$this->factoryProviderDefaultsProviderFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('? static fn(): callable => $factoryProviderDefaultsProviderFactory', $source);
        $this->assertStringContainsString('public function __invoke(): callable', $source);
        $this->assertStringContainsString('$factoryProviderDefaultsProviderFactory = ($this->factoryProviderDefaultsProviderFactoryProvider)();', $source);
        $this->assertStringContainsString('return static fn(', $source);
        $this->assertStringContainsString('application_adapter_registry $adapterRegistry', $source);
        $this->assertStringContainsString('): application_factory_provider_defaults_provider => $factoryProviderDefaultsProviderFactory($adapterRegistry);', $source);
        $this->assertStringContainsString('?? static fn(): callable => new application_factory_provider_defaults_provider_factory()', $source);
        $this->assertStringNotContainsString('private ?\Closure $factoryProviderDefaultsProviderFactory', $source);
        $this->assertStringNotContainsString('private function factoryProviderDefaultsProviderFactory(', $source);
        $this->assertStringNotContainsString('private function factoryProviderDefaultsProviderFactoryProvider(): callable', $source);
        $this->assertStringNotContainsString('(new \fan\core\di\application_factory_provider_defaults_provider_factory())(', $source);
        $this->assertStringContainsString('$adapterRegistry', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../factory/application_factory_provider_defaults_provider_factory.php';", $source);
        $this->assertStringNotContainsString('new application_container_defaults_factory(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_container_dependency_provider(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_registry_defaults_provider_factory(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_service_registrar_defaults_provider_factory(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_service_creator_defaults_provider_factory(', $source);
    }
}

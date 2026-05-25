<?php

declare(strict_types=1);

use fan\core\di\application_container_registrar_defaults_provider_factory;
use fan\core\di\application_service_registrar_defaults_provider;
use PHPUnit\Framework\TestCase;

final class ApplicationContainerRegistrarDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationContainerRegistrarDefaultFactory(): void
    {
        $factory = (new application_container_registrar_defaults_provider_factory())();

        $this->assertIsCallable($factory);
        $this->assertInstanceOf(application_service_registrar_defaults_provider::class, $factory());
    }

    public function testFactoryUsesInjectedRegistrarDefaultsProviderFactory(): void
    {
        $defaultFactory = (new application_container_registrar_defaults_provider_factory())();
        $expected = $defaultFactory();
        $factory = (new application_container_registrar_defaults_provider_factory(
            static fn(): application_service_registrar_defaults_provider => $expected
        ))();

        $this->assertSame($expected, $factory());
    }

    public function testFactoryUsesInjectedRegistrarDefaultsProviderFactoryProvider(): void
    {
        $expected = (new application_container_registrar_defaults_provider_factory())()();
        $calls = 0;
        $factory = (new application_container_registrar_defaults_provider_factory(
            registrarDefaultsProviderFactoryProvider: static function () use (&$calls, $expected): callable {
                return static function () use (&$calls, $expected): application_service_registrar_defaults_provider {
                    $calls++;

                    return $expected;
                };
            }
        ))();

        $this->assertSame($expected, $factory());
        $this->assertSame(1, $calls);
    }

    public function testSourceOwnsApplicationContainerRegistrarDefaultsCompositionBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_container_registrar_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class application_container_registrar_defaults_provider_factory', $source);
        $this->assertStringContainsString('private \Closure $registrarDefaultsProviderFactoryProvider;', $source);
        $this->assertStringContainsString('public function __construct(', $source);
        $this->assertStringContainsString('?callable $registrarDefaultsProviderFactoryProvider = null', $source);
        $this->assertStringContainsString('$this->registrarDefaultsProviderFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('? static fn(): callable => $registrarDefaultsProviderFactory', $source);
        $this->assertStringContainsString('public function __invoke(): callable', $source);
        $this->assertStringContainsString('$registrarDefaultsProviderFactory = ($this->registrarDefaultsProviderFactoryProvider)();', $source);
        $this->assertStringContainsString('return static fn(): application_service_registrar_defaults_provider => $registrarDefaultsProviderFactory();', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../factory/application_service_registrar_defaults_provider_factory.php';", $source);
        $this->assertStringContainsString('?? static fn(): callable => new application_service_registrar_defaults_provider_factory()', $source);
        $this->assertStringNotContainsString('private ?\Closure $registrarDefaultsProviderFactory', $source);
        $this->assertStringNotContainsString('private function registrarDefaultsProviderFactory(', $source);
        $this->assertStringNotContainsString('private function registrarDefaultsProviderFactoryProvider(): callable', $source);
        $this->assertStringNotContainsString('(new \fan\core\di\application_service_registrar_defaults_provider_factory())();', $source);
        $this->assertStringNotContainsString('new application_container_defaults_factory(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_container_dependency_provider(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_registry_defaults_provider_factory(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_factory_provider_defaults_provider_factory(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_service_creator_defaults_provider_factory(', $source);
    }
}

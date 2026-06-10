<?php

declare(strict_types=1);

use fan\core\di\application_container_creator_defaults_provider_factory;
use PHPUnit\Framework\TestCase;

final class ApplicationContainerCreatorDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationContainerCreatorDefaultFactory(): void
    {
        $factory = (new application_container_creator_defaults_provider_factory())();

        $this->assertIsCallable($factory);
        $this->assertIsArray($factory());
    }

    public function testFactoryUsesInjectedCreatorDefaultsProviderFactory(): void
    {
        $defaultFactory = (new application_container_creator_defaults_provider_factory())();
        $expected = $defaultFactory();
        $factory = (new application_container_creator_defaults_provider_factory(
            static fn(): array => $expected
        ))();

        $this->assertSame($expected, $factory());
    }

    public function testFactoryUsesInjectedCreatorDefaultsProviderFactoryProvider(): void
    {
        $expected = (new application_container_creator_defaults_provider_factory())()();
        $calls = 0;
        $factory = (new application_container_creator_defaults_provider_factory(
            creatorDefaultsProviderFactoryProvider: static function () use (&$calls, $expected): callable {
                return static function () use (&$calls, $expected): array {
                    $calls++;

                    return $expected;
                };
            }
        ))();

        $this->assertSame($expected, $factory());
        $this->assertSame(1, $calls);
    }

    public function testSourceOwnsApplicationContainerCreatorDefaultsCompositionBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_container_creator_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class application_container_creator_defaults_provider_factory', $source);
        $this->assertStringContainsString('private \Closure $creatorDefaultsProviderFactoryProvider;', $source);
        $this->assertStringContainsString('public function __construct(', $source);
        $this->assertStringContainsString('?callable $creatorDefaultsProviderFactoryProvider = null', $source);
        $this->assertStringContainsString('$this->creatorDefaultsProviderFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('? static fn(): callable => $creatorDefaultsProviderFactory', $source);
        $this->assertStringContainsString('public function __invoke(): callable', $source);
        $this->assertStringContainsString('$creatorDefaultsProviderFactory = ($this->creatorDefaultsProviderFactoryProvider)();', $source);
        $this->assertStringContainsString('return static fn(): array => $creatorDefaultsProviderFactory();', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../factory/application_service_creator_defaults_provider_factory.php';", $source);
        $this->assertStringContainsString('?? static fn(): callable => new application_service_creator_defaults_provider_factory()', $source);
        $this->assertStringNotContainsString('private ?\Closure $creatorDefaultsProviderFactory', $source);
        $this->assertStringNotContainsString('private function creatorDefaultsProviderFactory(', $source);
        $this->assertStringNotContainsString('private function creatorDefaultsProviderFactoryProvider(): callable', $source);
        $this->assertStringNotContainsString('(new \fan\core\di\application_service_creator_defaults_provider_factory())();', $source);
        $this->assertStringNotContainsString('new application_container_defaults_factory(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_container_dependency_provider(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_registry_defaults_provider_factory(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_factory_provider_defaults_provider_factory(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_service_registrar_defaults_provider_factory(', $source);
    }
}

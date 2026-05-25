<?php

declare(strict_types=1);

use fan\core\di\application_container_defaults_factory;
use PHPUnit\Framework\TestCase;

final class ApplicationContainerDefaultsFactoryTest extends TestCase
{
    public function testFactoryReturnsInjectedApplicationContainerFactory(): void
    {
        $containerFactory = static fn(): object => new stdClass();
        $factory = new application_container_defaults_factory(static fn(): callable => $containerFactory);

        $this->assertSame($containerFactory, $factory->containerFactory());
    }

    public function testFactoryUsesInjectedContainerFactoryProvider(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_container_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class application_container_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $containerFactory;', $source);
        $this->assertStringContainsString('public function __construct(callable $containerFactory)', $source);
        $this->assertStringContainsString('$this->containerFactory = \Closure::fromCallable($containerFactory);', $source);
        $this->assertStringContainsString('public function containerFactory(): callable', $source);
        $this->assertStringContainsString('return ($this->containerFactory)();', $source);
        $this->assertStringNotContainsString('public static function containerFactory(): callable', $source);
        $this->assertStringNotContainsString('containerClasses', $source);
        $this->assertStringNotContainsString('loadClass', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString('new bootstrap_operations_defaults_factory(', $source);
        $this->assertStringNotContainsString('new application_service_factory_options(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_container_dependency_provider()', $source);
        $this->assertStringNotContainsString('application_container_dependency_bundle::fromProvider(', $source);
        $this->assertStringNotContainsString('application_service_factory_bundle::fromProviders(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_container_factory(', $source);
    }
}

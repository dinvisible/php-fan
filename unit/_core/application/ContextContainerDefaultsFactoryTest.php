<?php

declare(strict_types=1);

use fan\core\bootstrap\context;
use fan\core\bootstrap\context_container_factory;
use fan\core\di\application_container_defaults_provider_factory;
use fan\core\di\container;
use fan\core\di\context_container_defaults_factory;
use fan\core\di\context_defaults_factory;
use PHPUnit\Framework\TestCase;

final class ContextContainerDefaultsFactoryTest extends TestCase
{
    public function testFactoryReturnsContextContainerFactory(): void
    {
        $containerFactory = $this->defaultsFactory()();
        $context = new context(defaultFactoriesFactory: new context_defaults_factory());

        $this->assertIsCallable($containerFactory);
        $this->assertInstanceOf(container::class, $containerFactory($context));
    }

    public function testFactoryUsesInjectedContextContainerAndApplicationContainerFactories(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/context_container_defaults_factory.php');
        $contextCoreSource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/context_core_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($contextCoreSource);
        $this->assertStringContainsString('final class context_container_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $contextContainerFactoryFactory;', $source);
        $this->assertStringContainsString('private \Closure $applicationContainerFactory;', $source);
        $this->assertStringContainsString('return ($this->contextContainerFactoryFactory)($this->applicationContainerFactory);', $source);
        $this->assertStringContainsString('new context_container_defaults_factory(', $contextCoreSource);
        $this->assertStringContainsString('(new application_container_defaults_provider_factory())()', $contextCoreSource);
        $this->assertStringNotContainsString('context_container_defaults_provider_factory', $contextCoreSource);
        $this->assertStringNotContainsString('context_application_container_defaults_provider_factory', $contextCoreSource);
    }

    private function defaultsFactory(): context_container_defaults_factory
    {
        $applicationContainerDefaults = (new application_container_defaults_provider_factory())();

        return new context_container_defaults_factory(
            static fn(callable $applicationContainerFactory): context_container_factory => new context_container_factory(
                $applicationContainerFactory
            ),
            $applicationContainerDefaults->containerFactory()
        );
    }
}

<?php

declare(strict_types=1);

use fan\core\di\application_container_operations_defaults_provider_factory;
use fan\core\bootstrap\context;
use PHPUnit\Framework\TestCase;
use fan\core\di\context_defaults_factory;


final class ApplicationContainerOperationsDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationContainerOperationsDefaultFactory(): void
    {
        $factory = (new application_container_operations_defaults_provider_factory())();
        $context = new context(defaultFactoriesFactory: new context_defaults_factory());

        $this->assertIsCallable($factory);
        $this->assertIsCallable($factory($context));
    }

    public function testFactoryAcceptsInjectedOperationsDefaultsProviderFactory(): void
    {
        $context = new context(defaultFactoriesFactory: new context_defaults_factory());
        $operationsFactory = static fn(): array => ['source' => 'application-container'];
        $provider = new class ($context, $operationsFactory) {
            public function __construct(private context $expectedContext, private mixed $operationsFactory)
            {
            }

            public function operationsFactory(context $context): callable
            {
                TestCase::assertSame($this->expectedContext, $context);

                return $this->operationsFactory;
            }
        };
        $providerFactory = static fn(): object => $provider;
        $factory = (new application_container_operations_defaults_provider_factory(
            static fn(): callable => $providerFactory
        ))();

        $this->assertSame($operationsFactory, $factory($context));
    }

    public function testSourceOwnsApplicationContainerOperationsDefaultsCompositionBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_container_operations_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class application_container_operations_defaults_provider_factory', $source);
        $this->assertStringContainsString('private \Closure $bootstrapOperationsDefaultsProviderFactoryProvider;', $source);
        $this->assertStringContainsString('?callable $bootstrapOperationsDefaultsProviderFactoryProvider = null', $source);
        $this->assertStringContainsString('$this->bootstrapOperationsDefaultsProviderFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('public function __invoke(): callable', $source);
        $this->assertStringContainsString('?? static fn(): callable => new bootstrap_operations_defaults_provider_factory()', $source);
        $this->assertStringContainsString('$bootstrapOperationsDefaultsProviderFactory = ($this->bootstrapOperationsDefaultsProviderFactoryProvider)();', $source);
        $this->assertStringContainsString('return static fn(context $context): callable => $bootstrapOperationsDefaultsProviderFactory()', $source);
        $this->assertStringContainsString('->operationsFactory($context);', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_operations_defaults_provider_factory.php';", $source);
        $this->assertStringNotContainsString('new application_container_defaults_factory(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_container_dependency_provider(', $source);
        $this->assertStringNotContainsString('new application_service_factory_options(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_container_factory(', $source);
    }
}

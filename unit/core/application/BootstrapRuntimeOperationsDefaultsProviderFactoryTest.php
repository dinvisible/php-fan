<?php

declare(strict_types=1);

use fan\core\di\bootstrap_runtime_operations_defaults_provider_factory;
use fan\core\bootstrap\context;
use PHPUnit\Framework\TestCase;
use fan\core\di\context_defaults_factory;


final class BootstrapRuntimeOperationsDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapRuntimeOperationsDefaultFactory(): void
    {
        $factory = (new bootstrap_runtime_operations_defaults_provider_factory())();
        $context = new context(defaultFactoriesFactory: new context_defaults_factory());

        $this->assertIsCallable($factory);
        $this->assertIsCallable($factory($context));
    }

    public function testFactoryAcceptsInjectedOperationsDefaultsProviderFactory(): void
    {
        $context = new context(defaultFactoriesFactory: new context_defaults_factory());
        $operationsFactory = static fn(): array => ['source' => 'runtime'];
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
        $factory = (new bootstrap_runtime_operations_defaults_provider_factory(
            static fn(): callable => $providerFactory
        ))();

        $this->assertSame($operationsFactory, $factory($context));
    }

    public function testSourceOwnsBootstrapRuntimeOperationsDefaultsCompositionBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/bootstrap_runtime_operations_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_runtime_operations_defaults_provider_factory', $source);
        $this->assertStringContainsString('private \Closure $bootstrapOperationsDefaultsProviderFactoryProvider;', $source);
        $this->assertStringContainsString('?callable $bootstrapOperationsDefaultsProviderFactoryProvider = null', $source);
        $this->assertStringContainsString('$this->bootstrapOperationsDefaultsProviderFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('public function __invoke(): callable', $source);
        $this->assertStringContainsString('?? static fn(): callable => new bootstrap_operations_defaults_provider_factory()', $source);
        $this->assertStringContainsString('$bootstrapOperationsDefaultsProviderFactory = ($this->bootstrapOperationsDefaultsProviderFactoryProvider)();', $source);
        $this->assertStringContainsString('return static fn(context $context): callable => $bootstrapOperationsDefaultsProviderFactory()', $source);
        $this->assertStringContainsString('->operationsFactory($context);', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_operations_defaults_provider_factory.php';", $source);
        $this->assertStringNotContainsString('new bootstrap_runtime_defaults_factory(', $source);
        $this->assertStringNotContainsString('new bootstrap_runtime_factory(', $source);
        $this->assertStringNotContainsString('new bootstrap_runtime_service_defaults_provider_factory()', $source);
        $this->assertStringNotContainsString('new bootstrap_runtime_state_defaults_provider_factory()', $source);
    }
}

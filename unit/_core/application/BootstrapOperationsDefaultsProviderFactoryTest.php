<?php

declare(strict_types=1);

use fan\core\di\bootstrap_operations_defaults_factory;
use fan\core\di\bootstrap_operations_defaults_provider_factory;
use fan\core\bootstrap\bootstrap_operations_factory;
use fan\core\bootstrap\context;
use fan\core\bootstrap\context_bootstrap_operations;
use PHPUnit\Framework\TestCase;
use fan\core\di\context_defaults_factory;


final class BootstrapOperationsDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapOperationsDefaultsProvider(): void
    {
        $provider = (new bootstrap_operations_defaults_provider_factory())();
        $context = new context(defaultFactoriesFactory: new context_defaults_factory());

        $this->assertInstanceOf(bootstrap_operations_defaults_factory::class, $provider);
        $this->assertIsCallable($provider->operationsFactory($context));
    }

    public function testFactoryAcceptsInjectedOperationDefaults(): void
    {
        $context = new context(defaultFactoriesFactory: new context_defaults_factory());
        $contextOperations = new context_bootstrap_operations($context);
        $operationsFactory = new bootstrap_operations_factory($contextOperations);

        $provider = (new bootstrap_operations_defaults_provider_factory(
            function (object $operations) use ($contextOperations, $operationsFactory): bootstrap_operations_factory {
                $this->assertSame($contextOperations, $operations);

                return $operationsFactory;
            },
            function (context $providedContext) use ($context, $contextOperations): context_bootstrap_operations {
                $this->assertSame($context, $providedContext);

                return $contextOperations;
            }
        ))();

        $this->assertSame($operationsFactory, $provider->operationsFactory($context));
    }

    public function testSourceOwnsBootstrapOperationsDefaultsCompositionBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/bootstrap_operations_defaults_provider_factory.php');
        $defaultsSource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/bootstrap_operations_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($defaultsSource);
        $this->assertStringContainsString('final class bootstrap_operations_defaults_provider_factory', $source);
        $this->assertStringContainsString('private \Closure $bootstrapOperationsFactoryFactory;', $source);
        $this->assertStringContainsString('private \Closure $contextBootstrapOperationsFactory;', $source);
        $this->assertStringContainsString('?callable $bootstrapOperationsFactoryFactory = null,', $source);
        $this->assertStringContainsString('?callable $contextBootstrapOperationsFactory = null', $source);
        $this->assertStringContainsString('$this->bootstrapOperationsFactoryFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->contextBootstrapOperationsFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('public function __invoke(): bootstrap_operations_defaults_factory', $source);
        $this->assertStringContainsString('return new bootstrap_operations_defaults_factory(', $source);
        $this->assertStringContainsString('static fn(object $operations): bootstrap_operations_factory => new bootstrap_operations_factory($operations)', $source);
        $this->assertStringContainsString('static fn(context $context): context_bootstrap_operations => new context_bootstrap_operations($context)', $source);
        $this->assertStringContainsString('$this->bootstrapOperationsFactoryFactory,', $source);
        $this->assertStringContainsString('$this->contextBootstrapOperationsFactory', $source);
        $this->assertStringNotContainsString("bootstrap_operations_defaults_factory::class => 'bootstrap_operations_defaults_factory.php'", $source);
        $this->assertStringNotContainsString("bootstrap_operations_factory::class => 'bootstrap_operations_factory.php'", $source);
        $this->assertStringNotContainsString("context_bootstrap_operations::class => 'context_bootstrap_operations.php'", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/' . \$fileName;", $source);
        $this->assertStringContainsString('private \Closure $bootstrapOperationsFactoryFactory;', $defaultsSource);
        $this->assertStringNotContainsString('new bootstrap_operations_factory(', $defaultsSource);
        $this->assertStringNotContainsString('new context_bootstrap_operations(', $defaultsSource);
    }
}

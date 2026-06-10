<?php

declare(strict_types=1);

use fan\core\di\bootstrap_operations_defaults_factory;
use fan\core\bootstrap\bootstrap_operations_factory;
use fan\core\bootstrap\context;
use fan\core\bootstrap\context_bootstrap_operations;
use PHPUnit\Framework\TestCase;
use fan\core\di\context_defaults_factory;


final class BootstrapOperationsDefaultsFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapOperationsFactory(): void
    {
        $context = new context(defaultFactoriesFactory: new context_defaults_factory());
        $operationsFactory = $this->defaultsFactory()->operationsFactory($context);

        $this->assertIsCallable($operationsFactory);
        $operations = $operationsFactory();

        foreach ([
            'getLoader',
            'getRunner',
            'getInitializer',
            'parsePath',
            'loadClass',
            'logError',
            'handleError',
            'getGlobalPath',
            'getConfigCache',
            'getPid',
            'isCli',
        ] as $operationName) {
            $this->assertArrayHasKey($operationName, $operations);
            $this->assertIsCallable($operations[$operationName]);
        }
    }

    public function testSourceOwnsContextBootstrapOperationsDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/bootstrap_operations_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_operations_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $bootstrapOperationsFactoryFactory;', $source);
        $this->assertStringContainsString('private \Closure $contextBootstrapOperationsFactory;', $source);
        $this->assertStringContainsString('public function __construct(callable $bootstrapOperationsFactoryFactory, callable $contextBootstrapOperationsFactory)', $source);
        $this->assertStringContainsString('$this->bootstrapOperationsFactoryFactory = \Closure::fromCallable($bootstrapOperationsFactoryFactory);', $source);
        $this->assertStringContainsString('$this->contextBootstrapOperationsFactory = \Closure::fromCallable($contextBootstrapOperationsFactory);', $source);
        $this->assertStringContainsString('public function operationsFactory(context $context): callable', $source);
        $this->assertStringContainsString('return ($this->bootstrapOperationsFactoryFactory)(', $source);
        $this->assertStringContainsString('($this->contextBootstrapOperationsFactory)($context)', $source);
        $this->assertStringNotContainsString('public static function operationsFactory(context $context): callable', $source);
        $this->assertStringNotContainsString('loadClass', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString('new bootstrap_operations_factory(new context_bootstrap_operations($context))', $source);
    }

    private function defaultsFactory(): bootstrap_operations_defaults_factory
    {
        return new bootstrap_operations_defaults_factory(
            static fn(object $operations): bootstrap_operations_factory => new bootstrap_operations_factory($operations),
            static fn(context $context): context_bootstrap_operations => new context_bootstrap_operations($context)
        );
    }
}

<?php

declare(strict_types=1);

use fan\core\bootstrap\context;
use fan\core\bootstrap\context_registry_state;
use PHPUnit\Framework\TestCase;
use fan\core\di\context_defaults_factory;


final class ContextRegistryStateTest extends TestCase
{
    public function testStateReturnsInjectedContext(): void
    {
        $context = self::context();
        $state = new context_registry_state($context);

        $this->assertSame($context, $state->context());
    }

    public function testStateCreatesContextThroughFactoryOnce(): void
    {
        $context = self::context();
        $calls = 0;
        $state = new context_registry_state(null, static function () use (&$calls, $context): context {
            $calls++;

            return $context;
        });

        $this->assertSame($context, $state->context());
        $this->assertSame($context, $state->context());
        $this->assertSame(1, $calls);
    }

    public function testStateRejectsInvalidFactoryResult(): void
    {
        $state = new context_registry_state(null, static fn(): object => new stdClass());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Bootstrap context factory must return a bootstrap context.');

        $state->context();
    }

    public function testStateRequiresContextOrFactory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Bootstrap context registry state requires a context or context factory.');

        new context_registry_state();
    }

    public function testStateDoesNotOwnDefaultContextFactoryBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/application/context_registry_state.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class context_registry_state', $source);
        $this->assertStringContainsString('private ?context $context;', $source);
        $this->assertStringContainsString('private \Closure $contextFactory;', $source);
        $this->assertStringContainsString('$this->contextFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('?? static fn(): context => $context', $source);
        $this->assertStringNotContainsString('private ?\Closure $contextFactory', $source);
        $this->assertStringNotContainsString('$contextFactory === null ? null : \Closure::fromCallable($contextFactory)', $source);
        $this->assertStringNotContainsString('Bootstrap context registry state has no context factory.', $source);
        $this->assertStringNotContainsString('public function setContext(', $source);
        $this->assertStringNotContainsString('public function setContextFactory(', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/context_factory.php';", $source);
        $this->assertStringNotContainsString('new context_factory()', $source);
        $this->assertStringNotContainsString('defaultContextFactory', $source);
    }

    private static function context(): context
    {
        return new context(defaultFactoriesFactory: new context_defaults_factory());
    }
}

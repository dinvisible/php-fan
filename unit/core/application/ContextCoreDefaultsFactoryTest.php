<?php

declare(strict_types=1);

use fan\core\di\context_core_defaults_factory;
use PHPUnit\Framework\TestCase;

final class ContextCoreDefaultsFactoryTest extends TestCase
{
    public function testFactoryReturnsInjectedCoreContextDefaults(): void
    {
        $defaults = [
            'stateFactory' => static fn(): object => new stdClass(),
            'containerFactory' => static fn(): object => new stdClass(),
            'requestInputFactory' => static fn(): object => new stdClass(),
            'bootstrapRuntimeFactory' => static fn(): object => new stdClass(),
        ];
        $factory = new context_core_defaults_factory(static fn(): array => $defaults);

        $this->assertSame($defaults, $factory());
    }

    public function testFactoryUsesInjectedCoreContextDefaultsFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/context_core_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class context_core_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $contextCoreDefaultsFactory;', $source);
        $this->assertStringContainsString('public function __construct(callable $contextCoreDefaultsFactory)', $source);
        $this->assertStringContainsString('$this->contextCoreDefaultsFactory = \Closure::fromCallable($contextCoreDefaultsFactory);', $source);
        $this->assertStringContainsString('return ($this->contextCoreDefaultsFactory)();', $source);
        $this->assertStringNotContainsString('loadDefaultFactories', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString('new bootstrap_state_defaults_provider_factory()', $source);
        $this->assertStringNotContainsString('new context_container_defaults_factory(', $source);
        $this->assertStringNotContainsString('new bootstrap_runtime_defaults_factory(', $source);
        $this->assertStringNotContainsString('new bootstrap_request_input_defaults_provider_factory()', $source);
    }
}

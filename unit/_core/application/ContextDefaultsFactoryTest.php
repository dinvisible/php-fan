<?php

declare(strict_types=1);

use fan\core\di\context_defaults_factory;
use PHPUnit\Framework\TestCase;

final class ContextDefaultsFactoryTest extends TestCase
{
    public function testFactoryReturnsInjectedDefaultContextFactories(): void
    {
        $defaults = [
            'stateFactory' => static fn(): object => new stdClass(),
            'containerFactory' => static fn(): object => new stdClass(),
            'requestInputFactory' => static fn(): object => new stdClass(),
            'bootstrapRuntimeFactory' => static fn(): object => new stdClass(),
            'zendAutoloaderLoaderFactory' => static fn(): object => new stdClass(),
            'bootstrapLoaderFileStorageFactory' => static fn(): object => new stdClass(),
            'bootstrapObjectFactory' => static fn(string $class, array $arguments): object => new stdClass(),
            'bootstrapConfigLoader' => static fn(): null => null,
            'bootstrapErrorHandlerSetup' => static fn(): null => null,
            'phpRuntimeSettingsFactory' => static fn(): object => new stdClass(),
            'errorHandlerRegistrar' => static fn(): null => null,
            'errorHandlerSetup' => static fn(): null => null,
            'errorLogger' => static fn(): null => null,
        ];
        $factory = new context_defaults_factory(static fn(): array => $defaults);

        $this->assertSame($defaults, $factory());
    }

    public function testFactoryUsesInjectedContextDefaultsFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/context_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class context_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $contextDefaultsFactory;', $source);
        $this->assertStringContainsString('public function __construct(?callable $contextDefaultsFactory = null)', $source);
        $this->assertStringContainsString('$this->contextDefaultsFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('return ($this->contextDefaultsFactory)();', $source);
        $this->assertStringNotContainsString('loadDefaultFactories', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString('new context_core_defaults_factory()', $source);
        $this->assertStringNotContainsString('new error_handling_defaults_factory(', $source);
        $this->assertStringContainsString('new context_support_defaults_provider_factory()', $source);
        $this->assertStringNotContainsString('new bootstrap_error_handler_setup()', $source);
        $this->assertStringNotContainsString('new php_runtime_settings()', $source);
        $this->assertStringNotContainsString('new error_handler_registrar()', $source);
        $this->assertStringNotContainsString('new error_handler_setup(', $source);
        $this->assertStringNotContainsString('new error_logger_defaults_provider_factory()', $source);
    }
}

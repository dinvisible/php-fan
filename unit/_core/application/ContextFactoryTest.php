<?php

declare(strict_types=1);

use fan\core\bootstrap\context;
use fan\core\bootstrap\context_factory;
use PHPUnit\Framework\TestCase;
use fan\core\di\context_defaults_factory;
use fan\core\bootstrap\state;
use fan\core\di\container;


final class ContextFactoryTest extends TestCase
{
    public function testFactoryCreatesDefaultBootstrapContext(): void
    {
        $factory = new context_factory(new context_defaults_factory());

        $this->assertInstanceOf(context::class, $factory());
    }

    public function testFactoryPassesInjectedDefaultsFactoryToContext(): void
    {
        $factory = new context_factory(static fn(): array => [
            'stateFactory' => static fn(): state => new state(),
            'containerFactory' => static fn(): container => new container(),
            'requestInputFactory' => static fn(): object => new stdClass(),
            'bootstrapRuntimeFactory' => static fn(): object => new stdClass(),
            'zendAutoloaderLoaderFactory' => static fn(): object => new class {
                public function load(string $zendPath): void
                {
                }
            },
            'bootstrapLoaderFileStorageFactory' => static fn(): object => new stdClass(),
            'bootstrapObjectFactory' => static fn(string $class, array $arguments): object => new stdClass(),
            'bootstrapConfigLoader' => static fn(context $context, ?string $configPath = null): null => null,
            'bootstrapErrorHandlerSetup' => static fn(context $context, callable $handler): null => null,
            'phpRuntimeSettingsFactory' => static fn(): object => new stdClass(),
            'errorHandlerRegistrar' => static fn(callable $handler): null => null,
            'errorHandlerSetup' => static fn(callable $handler, string $defaultTimezone): null => null,
            'errorLogger' => static fn(string $message, string $logDir): null => null,
        ]);

        $this->assertInstanceOf(context::class, $factory());
    }

    public function testFactoryOwnsConcreteDefaultContextDependencies(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/context_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class context_factory', $source);
        $this->assertStringContainsString('public function __construct(callable $defaultFactoriesFactory)', $source);
        $this->assertStringContainsString('\Closure::fromCallable($defaultFactoriesFactory)', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/context.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/state.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/context_defaults_factory.php';", $source);
        $this->assertStringNotContainsString('new context_defaults_factory()', $source);
        $this->assertStringNotContainsString('defaultContextFactoriesFactory', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/request_input_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_runtime_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/zend_autoloader_loader_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_object_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/error_handler_setup.php';", $source);
        $this->assertStringContainsString('return new context(defaultFactoriesFactory: $this->defaultFactoriesFactory);', $source);
    }
}

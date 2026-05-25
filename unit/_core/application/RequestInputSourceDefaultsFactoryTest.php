<?php

declare(strict_types=1);

use fan\core\runtime\request_input_source_defaults_factory;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\request_input_globals_factory;
use fan\core\runtime\request_input_source_factory;
use fan\core\service\request_input_source;


final class BootstrapRequestInputSourceDefaultsFactoryTest extends TestCase
{
    public function testFactoryCreatesRequestInputSourceFactoryFromInjectedEnvironmentFactory(): void
    {
        $environment = new stdClass();
        $factory = new request_input_source_defaults_factory(
            static fn(): callable => new request_input_source_factory(
                new request_input_globals_factory(static fn(): object => $environment)
            )
        );
        $sourceFactory = $factory->sourceFactory();

        $this->assertIsCallable($sourceFactory);
        $this->assertInstanceOf(request_input_source::class, $sourceFactory());
    }

    public function testSourceOwnsRequestInputSourceDefaultAssemblyBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/runtime/request_input_source_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class request_input_source_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $sourceFactoryFactory;', $source);
        $this->assertStringContainsString('public function __construct(callable $sourceFactoryFactory)', $source);
        $this->assertStringContainsString('$this->sourceFactoryFactory = \Closure::fromCallable($sourceFactoryFactory);', $source);
        $this->assertStringContainsString('public function sourceFactory(): callable', $source);
        $this->assertStringContainsString('return ($this->sourceFactoryFactory)();', $source);
        $this->assertStringNotContainsString('public static function sourceFactory(callable $environmentFactory): callable', $source);
        $this->assertStringNotContainsString('loadClass', $source);
        $this->assertStringNotContainsString('sourceClasses', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString('new request_input_source_factory(', $source);
        $this->assertStringNotContainsString('new request_input_globals_factory($environmentFactory)', $source);
        $this->assertStringNotContainsString('request_input_native_environment', $source);
    }
}

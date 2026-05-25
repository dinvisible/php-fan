<?php

declare(strict_types=1);

use fan\core\runtime\request_input_defaults_factory;
use fan\core\runtime\request_input_factory;
use PHPUnit\Framework\TestCase;
use fan\core\service\request_input;


final class BootstrapRequestInputDefaultsFactoryTest extends TestCase
{
    public function testFactoryBuildsRequestInputFactoryFromInjectedEnvironmentFactory(): void
    {
        $source = new stdClass();
        $factory = new request_input_defaults_factory(
            static fn(callable $sourceFactory): request_input_factory => new request_input_factory($sourceFactory),
            static fn(): object => $source
        );
        $inputFactory = $factory();

        $this->assertIsCallable($inputFactory);
        $this->assertInstanceOf(request_input::class, $inputFactory());
    }

    public function testSourceOwnsRequestInputDefaultAssemblyBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/runtime/request_input_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private \Closure $requestInputFactoryFactory;', $source);
        $this->assertStringContainsString('private \Closure $requestInputSourceFactory;', $source);
        $this->assertStringContainsString('public function __construct(callable $requestInputFactoryFactory, callable $requestInputSourceFactory)', $source);
        $this->assertStringContainsString('$this->requestInputFactoryFactory = \Closure::fromCallable($requestInputFactoryFactory);', $source);
        $this->assertStringContainsString('$this->requestInputSourceFactory = \Closure::fromCallable($requestInputSourceFactory);', $source);
        $this->assertStringContainsString('return ($this->requestInputFactoryFactory)($this->requestInputSourceFactory);', $source);
        $this->assertStringNotContainsString('return new request_input_factory(', $source);
        $this->assertStringNotContainsString('request_input_source_defaults_factory::sourceFactory($this->environmentFactory)', $source);
        $this->assertStringNotContainsString('request_input_source_defaults_factory::sourceFactory(', $source);
        $this->assertStringNotContainsString('new request_input_source_factory(', $source);
        $this->assertStringNotContainsString('new request_input_globals_factory($this->environmentFactory)', $source);
        $this->assertStringNotContainsString('private function loadDefaultFactories(): void', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/request_input_source_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/request_input_globals_factory.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/service/request_input_source.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/adapter/request_input_globals.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/adapter/request_input_native_environment.php';", $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\request_input_native_environment()', $source);
    }
}

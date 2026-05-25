<?php

declare(strict_types=1);

use fan\core\di\bootstrap_request_input_defaults_factory;
use fan\core\runtime\request_input_defaults_factory;
use fan\core\runtime\request_input_factory;
use PHPUnit\Framework\TestCase;
use fan\core\service\request_input;


final class BootstrapContextRequestInputDefaultsFactoryTest extends TestCase
{
    public function testFactoryCreatesRequestInputFactory(): void
    {
        $defaultsFactory = new bootstrap_request_input_defaults_factory(
            static fn(): callable => (new request_input_defaults_factory(
                static fn(callable $sourceFactory): request_input_factory => new request_input_factory($sourceFactory),
                static fn(): object => new stdClass()
            ))()
        );
        $requestInputFactory = $defaultsFactory->requestInputFactory();

        $this->assertIsCallable($requestInputFactory);
        $this->assertInstanceOf(request_input::class, $requestInputFactory());
    }

    public function testSourceOwnsBootstrapRequestInputDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/bootstrap_request_input_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_request_input_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $requestInputFactoryFactory;', $source);
        $this->assertStringContainsString('public function __construct(?callable $requestInputFactoryFactory = null)', $source);
        $this->assertStringContainsString('$requestInputDefaults = (new request_input_defaults_provider_factory())();', $source);
        $this->assertStringContainsString('public function requestInputFactory(): callable', $source);
        $this->assertStringContainsString('return ($this->requestInputFactoryFactory)();', $source);
        $this->assertStringNotContainsString('public static function requestInputFactory(): callable', $source);
        $this->assertStringNotContainsString('loadClass', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString('new request_input_defaults_factory(', $source);
        $this->assertStringNotContainsString('new request_input_factory(', $source);
        $this->assertStringNotContainsString('request_input_source_defaults_factory::sourceFactory(', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\request_input_native_environment()', $source);
    }
}

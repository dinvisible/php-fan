<?php

declare(strict_types=1);

use fan\core\runtime\request_input_factory;
use PHPUnit\Framework\TestCase;
use fan\core\service\request_input;


final class BootstrapRequestInputFactoryTest extends TestCase
{
    public function testFactoryCreatesRequestInputBoundaryObject(): void
    {
        $source = new stdClass();
        $factory = new request_input_factory(static fn(): object => $source);

        $this->assertInstanceOf(request_input::class, $factory());
    }

    public function testFactoryRejectsInvalidInjectedSource(): void
    {
        $factory = new request_input_factory(static fn(): string => 'not-source');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Request input source factory must return an object.');

        $factory();
    }

    public function testSourceOnlyOwnsRequestInputBoundaryConstruction(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/runtime/request_input_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class request_input_factory', $source);
        $this->assertStringContainsString('public function __construct(callable $sourceFactory)', $source);
        $this->assertStringContainsString('$this->sourceFactory = \Closure::fromCallable($sourceFactory);', $source);
        $this->assertStringContainsString('$source = ($this->sourceFactory)();', $source);
        $this->assertStringContainsString('new request_input($source)', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString('request_input_source', $source);
        $this->assertStringNotContainsString('request_input_globals', $source);
        $this->assertStringNotContainsString('request_input_native_environment', $source);
    }
}

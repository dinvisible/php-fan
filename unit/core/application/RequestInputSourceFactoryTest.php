<?php

declare(strict_types=1);

use fan\core\runtime\request_input_source_factory;
use PHPUnit\Framework\TestCase;
use fan\core\service\request_input_source;


final class BootstrapRequestInputSourceFactoryTest extends TestCase
{
    public function testFactoryCreatesRequestInputSourceFromInjectedGlobals(): void
    {
        $globals = new stdClass();
        $factory = new request_input_source_factory(static fn(): object => $globals);

        $this->assertInstanceOf(request_input_source::class, $factory());
    }

    public function testFactoryUsesInjectedSourceProvider(): void
    {
        $globals = new stdClass();
        $expected = (object)['name' => 'source'];
        $receivedGlobals = null;
        $factory = new request_input_source_factory(
            static fn(): object => $globals,
            static function (object $received) use (&$receivedGlobals, $expected): object {
                $receivedGlobals = $received;

                return $expected;
            }
        );

        $this->assertSame($expected, $factory());
        $this->assertSame($globals, $receivedGlobals);
    }

    public function testFactoryRejectsInvalidInjectedGlobals(): void
    {
        $factory = new request_input_source_factory(static fn(): string => 'not-globals');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Request input globals factory must return an object.');

        $factory();
    }

    public function testSourceUsesInjectedGlobalsFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/runtime/request_input_source_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private \Closure $sourceProvider;', $source);
        $this->assertStringContainsString('public function __construct(callable $globalsFactory, ?callable $sourceProvider = null)', $source);
        $this->assertStringContainsString('$this->globalsFactory = \Closure::fromCallable($globalsFactory);', $source);
        $this->assertStringContainsString('$this->sourceProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$globals = ($this->globalsFactory)();', $source);
        $this->assertStringContainsString('$sourceProvider = $this->sourceProvider;', $source);
        $this->assertStringContainsString('return $sourceProvider($globals);', $source);
        $this->assertStringContainsString('?? static fn(object $globals): object => new request_input_source($globals)', $source);
        $this->assertStringNotContainsString('private ?\Closure $sourceProvider', $source);
        $this->assertStringNotContainsString('$sourceProvider === null ? null : \Closure::fromCallable($sourceProvider)', $source);
        $this->assertStringNotContainsString('private function sourceProvider(): callable', $source);
        $this->assertStringNotContainsString('return new \fan\core\service\request_input_source($globals);', $source);
        $this->assertStringNotContainsString('request_input_native_environment', $source);
        $this->assertStringNotContainsString('request_input_globals(', $source);
        $this->assertStringNotContainsString('require_once', $source);
    }
}

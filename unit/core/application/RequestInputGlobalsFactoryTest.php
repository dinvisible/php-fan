<?php

declare(strict_types=1);

use fan\core\adapter\request_input_globals_factory;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\request_input_globals;


final class BootstrapRequestInputGlobalsFactoryTest extends TestCase
{
    public function testFactoryCreatesRequestInputGlobalsFromInjectedEnvironment(): void
    {
        $environment = new stdClass();
        $factory = new request_input_globals_factory(static fn(): object => $environment);

        $this->assertInstanceOf(request_input_globals::class, $factory());
    }

    public function testFactoryUsesInjectedGlobalsProvider(): void
    {
        $environment = new stdClass();
        $expected = (object)['name' => 'globals'];
        $receivedEnvironment = null;
        $factory = new request_input_globals_factory(
            static fn(): object => $environment,
            static function (object $received) use (&$receivedEnvironment, $expected): object {
                $receivedEnvironment = $received;

                return $expected;
            }
        );

        $this->assertSame($expected, $factory());
        $this->assertSame($environment, $receivedEnvironment);
    }

    public function testFactoryRejectsInvalidInjectedEnvironment(): void
    {
        $factory = new request_input_globals_factory(static fn(): string => 'not-environment');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Request input environment factory must return an object.');

        $factory();
    }

    public function testSourceUsesInjectedEnvironmentFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/adapter/request_input_globals_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private \Closure $globalsProvider;', $source);
        $this->assertStringContainsString('public function __construct(callable $environmentFactory, ?callable $globalsProvider = null)', $source);
        $this->assertStringContainsString('$this->environmentFactory = \Closure::fromCallable($environmentFactory);', $source);
        $this->assertStringContainsString('$this->globalsProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$environment = ($this->environmentFactory)();', $source);
        $this->assertStringContainsString('$globalsProvider = $this->globalsProvider;', $source);
        $this->assertStringContainsString('return $globalsProvider($environment);', $source);
        $this->assertStringContainsString('?? static fn(object $environment): object => new request_input_globals($environment)', $source);
        $this->assertStringNotContainsString('private ?\Closure $globalsProvider', $source);
        $this->assertStringNotContainsString('$globalsProvider === null ? null : \Closure::fromCallable($globalsProvider)', $source);
        $this->assertStringNotContainsString('private function globalsProvider(): callable', $source);
        $this->assertStringNotContainsString('return new \fan\core\adapter\request_input_globals($environment);', $source);
        $this->assertStringNotContainsString('request_input_native_environment', $source);
        $this->assertStringNotContainsString('require_once', $source);
    }
}

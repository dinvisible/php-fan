<?php

declare(strict_types=1);

use fan\core\di\bootstrap_state_defaults_factory;
use fan\core\bootstrap\state;
use PHPUnit\Framework\TestCase;

final class BootstrapStateDefaultsFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapStateFactory(): void
    {
        $factory = new bootstrap_state_defaults_factory(
            static fn(): callable => static fn(): state => new state()
        );

        $this->assertInstanceOf(state::class, $factory->stateFactory()());
    }

    public function testSourceOwnsBootstrapStateDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/bootstrap_state_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_state_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $stateFactory;', $source);
        $this->assertStringContainsString('public function __construct(?callable $stateFactory = null)', $source);
        $this->assertStringContainsString('?? static fn(): callable => static fn(): state => new state()', $source);
        $this->assertStringContainsString('public function stateFactory(): callable', $source);
        $this->assertStringContainsString('return ($this->stateFactory)();', $source);
        $this->assertStringNotContainsString('public static function stateFactory(): callable', $source);
        $this->assertStringNotContainsString('loadClass', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringContainsString('new state()', $source);
    }
}

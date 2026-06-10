<?php

declare(strict_types=1);

use fan\core\di\bootstrap_config_defaults_factory;
use fan\core\bootstrap\bootstrap_config_loader;
use PHPUnit\Framework\TestCase;

final class BootstrapConfigDefaultsFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapConfigLoader(): void
    {
        $factory = new bootstrap_config_defaults_factory(
            static fn(): callable => new bootstrap_config_loader()
        );

        $this->assertInstanceOf(
            bootstrap_config_loader::class,
            $factory->configLoader()
        );
    }

    public function testSourceOwnsBootstrapConfigLoaderDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/bootstrap_config_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_config_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $configLoaderFactory;', $source);
        $this->assertStringContainsString('public function __construct(?callable $configLoaderFactory = null)', $source);
        $this->assertStringContainsString('?? static fn(): callable => new bootstrap_config_loader()', $source);
        $this->assertStringContainsString('public function configLoader(): callable', $source);
        $this->assertStringContainsString('return ($this->configLoaderFactory)();', $source);
        $this->assertStringNotContainsString('public static function configLoader(): callable', $source);
        $this->assertStringNotContainsString('loadClass', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringContainsString('new bootstrap_config_loader()', $source);
    }
}

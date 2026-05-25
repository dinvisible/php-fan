<?php

declare(strict_types=1);

use fan\core\di\bootstrap_autoloader_defaults_factory;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\zend_autoloader_loader;


final class BootstrapAutoloaderDefaultsFactoryTest extends TestCase
{
    public function testFactoryCreatesZendAutoloaderLoader(): void
    {
        $factory = new bootstrap_autoloader_defaults_factory(
            static fn(): object => new zend_autoloader_loader()
        );

        $this->assertInstanceOf(
            zend_autoloader_loader::class,
            $factory->zendAutoloaderLoader()
        );
    }

    public function testSourceOwnsZendAutoloaderLoaderDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/bootstrap_autoloader_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_autoloader_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $zendAutoloaderLoaderFactory;', $source);
        $this->assertStringContainsString('public function __construct(callable $zendAutoloaderLoaderFactory)', $source);
        $this->assertStringContainsString('$this->zendAutoloaderLoaderFactory = \Closure::fromCallable($zendAutoloaderLoaderFactory);', $source);
        $this->assertStringContainsString('public function zendAutoloaderLoader(): object', $source);
        $this->assertStringContainsString('return ($this->zendAutoloaderLoaderFactory)();', $source);
        $this->assertStringNotContainsString('public static function zendAutoloaderLoader(): object', $source);
        $this->assertStringNotContainsString('loadClass', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\zend_autoloader_loader()', $source);
    }
}

<?php

declare(strict_types=1);

use fan\core\bootstrap\application;
use fan\core\di\application_defaults_factory;
use fan\core\di\bootstrap_application_defaults_factory;
use PHPUnit\Framework\TestCase;

final class BootstrapApplicationDefaultsFactoryTest extends TestCase
{
    public function testFactoryCreatesDefaultApplication(): void
    {
        $factory = $this->defaultsFactory();

        $this->assertInstanceOf(application::class, $factory->application());
        $this->assertInstanceOf(
            application_defaults_factory::class,
            $factory->applicationDefaults()
        );
    }

    public function testSourceOwnsBootstrapFacadeApplicationDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/bootstrap_application_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_application_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $applicationDefaultsFactoryFactory;', $source);
        $this->assertStringContainsString('private \Closure $applicationFactory;', $source);
        $this->assertStringContainsString('public function __construct(?callable $applicationDefaultsFactoryFactory = null, ?callable $applicationFactory = null)', $source);
        $this->assertStringContainsString('?? static fn(callable $applicationFactory): application_defaults_factory => new application_defaults_factory($applicationFactory)', $source);
        $this->assertStringContainsString('?? static fn(): application => new application()', $source);
        $this->assertStringContainsString('return ($this->applicationDefaultsFactoryFactory)($this->applicationFactory);', $source);
        $this->assertStringContainsString('return ($this->applicationFactory)();', $source);
        $this->assertStringNotContainsString('public static function applicationDefaults()', $source);
        $this->assertStringNotContainsString('public static function application()', $source);
        $this->assertStringNotContainsString('loadClass', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringContainsString('new application_defaults_factory', $source);
        $this->assertStringContainsString('new application()', $source);
        $this->assertFileDoesNotExist(dirname(__DIR__, 3) . '/core/bootstrap.php');
    }

    private function defaultsFactory(): bootstrap_application_defaults_factory
    {
        return new bootstrap_application_defaults_factory(
            static fn(callable $applicationFactory): application_defaults_factory => new application_defaults_factory($applicationFactory),
            static fn(): application => new application()
        );
    }
}

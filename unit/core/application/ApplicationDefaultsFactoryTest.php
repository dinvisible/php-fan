<?php

declare(strict_types=1);

use fan\core\bootstrap\application;
use fan\core\di\application_defaults_factory;
use PHPUnit\Framework\TestCase;

final class ApplicationDefaultsFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationAndErrorHandlerFactories(): void
    {
        $factory = new application_defaults_factory(static fn(): application => new application());
        $applicationFactory = $factory->applicationFactory();
        $errorHandlerFactory = $factory->errorHandlerFactory();

        $application = $applicationFactory();
        $errorHandler = $errorHandlerFactory($application);

        $this->assertInstanceOf(application::class, $application);
        $this->assertIsCallable($errorHandler);
        $this->assertTrue($errorHandler(E_DEPRECATED, 'deprecated'));
    }

    public function testSourceOwnsApplicationDefaults(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class application_defaults_factory', $source);
        $this->assertStringContainsString('public function __construct(callable $applicationFactory)', $source);
        $this->assertStringContainsString('$this->applicationFactory = \Closure::fromCallable($applicationFactory);', $source);
        $this->assertStringContainsString('return $this->applicationFactory;', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/application.php';", $source);
        $this->assertStringNotContainsString('new application()', $source);
        $this->assertStringContainsString('$application->logError(', $source);
    }
}

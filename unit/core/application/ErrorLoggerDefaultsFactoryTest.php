<?php

declare(strict_types=1);

use fan\core\runtime\error_logger_defaults_factory;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\error_logger_file_storage;
use fan\core\adapter\error_log_writer;
use fan\core\runtime\error_logger;


final class ErrorLoggerDefaultsFactoryTest extends TestCase
{
    public function testFactoryCreatesCallableErrorLogger(): void
    {
        $factory = new error_logger_defaults_factory(
            static fn(): callable => new error_logger(
                new error_log_writer(),
                new error_logger_file_storage()
            )
        );
        $errorLogger = $factory->errorLogger();

        $this->assertIsCallable($errorLogger);
        $this->assertInstanceOf(error_logger::class, $errorLogger);
    }

    public function testSourceOwnsErrorLoggerDefaultAssemblyBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/runtime/error_logger_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class error_logger_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $errorLoggerFactory;', $source);
        $this->assertStringContainsString('public function __construct(callable $errorLoggerFactory)', $source);
        $this->assertStringContainsString('$this->errorLoggerFactory = \Closure::fromCallable($errorLoggerFactory);', $source);
        $this->assertStringContainsString('public function errorLogger(): callable', $source);
        $this->assertStringContainsString('return ($this->errorLoggerFactory)();', $source);
        $this->assertStringNotContainsString('public static function errorLogger(): callable', $source);
        $this->assertStringNotContainsString('loadClass', $source);
        $this->assertStringNotContainsString('loggerClasses', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString('new error_logger(', $source);
        $this->assertStringNotContainsString('new error_log_writer()', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\error_logger_file_storage()', $source);
    }
}

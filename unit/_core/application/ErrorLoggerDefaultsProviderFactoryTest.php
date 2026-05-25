<?php

declare(strict_types=1);

use fan\core\runtime\error_logger_defaults_factory;
use fan\core\di\error_logger_defaults_provider_factory;
use PHPUnit\Framework\TestCase;
use fan\core\runtime\error_logger;


final class ErrorLoggerDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesErrorLoggerDefaultsProvider(): void
    {
        $provider = (new error_logger_defaults_provider_factory())();
        $errorLogger = $provider->errorLogger();

        $this->assertInstanceOf(error_logger_defaults_factory::class, $provider);
        $this->assertIsCallable($errorLogger);
        $this->assertInstanceOf(error_logger::class, $errorLogger);
    }

    public function testFactoryUsesInjectedErrorLoggerFactoryProvider(): void
    {
        $logger = static function (string $message, string $logDir): void {
        };
        $providerCalls = 0;
        $provider = (new error_logger_defaults_provider_factory(
            static function () use (&$providerCalls, $logger): callable {
                ++$providerCalls;

                return $logger;
            }
        ))();

        $this->assertSame($logger, $provider->errorLogger());
        $this->assertSame(1, $providerCalls);
    }

    public function testSourceOwnsErrorLoggerDefaultAssemblyBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/error_logger_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class error_logger_defaults_provider_factory', $source);
        $this->assertStringContainsString('private \Closure $errorLoggerFactoryProvider;', $source);
        $this->assertStringContainsString('public function __construct(?callable $errorLoggerFactoryProvider = null)', $source);
        $this->assertStringContainsString('$this->errorLoggerFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('public function __invoke(): error_logger_defaults_factory', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/error_logger_defaults_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/error_logger.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../adapter/error_log_writer.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../adapter/error_logger_file_storage.php';", $source);
        $this->assertStringContainsString('return new error_logger_defaults_factory(', $source);
        $this->assertStringContainsString('$errorLoggerFactoryProvider = $this->errorLoggerFactoryProvider;', $source);
        $this->assertStringContainsString('$errorLoggerFactoryProvider', $source);
        $this->assertStringContainsString('new error_logger(', $source);
        $this->assertStringContainsString('new error_log_writer()', $source);
        $this->assertStringContainsString('new error_logger_file_storage()', $source);
        $this->assertStringNotContainsString('return new error_logger_defaults_factory(' . "\n" . '            static fn(): callable => new error_logger(', $source);
    }
}

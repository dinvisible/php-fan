<?php

declare(strict_types=1);

use fan\core\adapter\error_log_writer;
use Psr\Log\AbstractLogger;
use PHPUnit\Framework\TestCase;
use Monolog\Level;


final class ErrorLogWriterTest extends TestCase
{
    public function testSourceOwnsNativeErrorLogBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/adapter/error_log_writer.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class error_log_writer', $source);
        $this->assertStringContainsString('use Monolog\Logger;', $source);
        $this->assertStringContainsString('use Monolog\Handler\StreamHandler;', $source);
        $this->assertStringContainsString('return error_log($message, $messageType)', $source);
        $this->assertStringContainsString('return error_log($message, $messageType, $destination)', $source);
    }

    public function testWriterExposesWriteMethod(): void
    {
        $writer = new error_log_writer();

        $this->assertTrue(method_exists($writer, 'write'));
        $this->assertTrue(method_exists($writer, 'logError'));
    }

    public function testWriterDelegatesDefaultErrorWritesToConfiguredLogger(): void
    {
        $logger = new ErrorLogWriterLoggerDouble();
        $writer = new error_log_writer(['ENABLED' => '1', 'LEVEL' => 'error'], null, $logger);

        $this->assertTrue($writer->write('Monolog message'));
        $writer->logError('Log error message');

        $this->assertSame([
            ['error', 'Monolog message', []],
            ['error', 'Log error message', []],
        ], $logger->records);
    }
}

final class ErrorLogWriterLoggerDouble extends AbstractLogger
{
    public array $records = [];

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $levelName = $level instanceof Level ? strtolower($level->getName()) : (string)$level;
        $this->records[] = [$levelName, (string)$message, $context];
    }
}

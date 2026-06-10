<?php

declare(strict_types=1);

use fan\core\runtime\error_logger;
use PHPUnit\Framework\TestCase;

final class ErrorLoggerTest extends TestCase
{
    public function testSourceUsesInjectedWriterAndFileStorage(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/runtime/error_logger.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class error_logger', $source);
        $this->assertStringContainsString('private object $errorLogWriter,', $source);
        $this->assertStringContainsString('private object $fileStorage', $source);
        $this->assertStringContainsString('$this->errorLogWriter->write($row, 3, $logPath)', $source);
        $this->assertStringContainsString('$this->errorLogWriter->write($message, 0)', $source);
        $this->assertStringContainsString('$this->fileStorage->isDirectory($logDir)', $source);
        $this->assertStringNotContainsString('new error_log_writer()', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString('error_log(', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_dir|is_writable|file_exists)\s*\(/',
            $source
        );
    }

    public function testLoggerIsCallable(): void
    {
        $logger = new error_logger(new ErrorLoggerWriterDouble(), new ErrorLoggerFileStorageDouble());

        $this->assertIsCallable($logger);
    }

    public function testLoggerWritesThroughInjectedWriter(): void
    {
        $writer = new ErrorLoggerWriterDouble();
        $fileStorage = new ErrorLoggerFileStorageDouble();
        $logger = new error_logger($writer, $fileStorage);
        $logDir = '/var/log/php-fan';

        $fileStorage->directories[$logDir] = true;
        $logger('message with tab' . "\t" . 'and newline' . "\n", $logDir);

        $fileStorage->writable[$logDir] = false;
        $logger('fallback message', $logDir);

        $this->assertCount(2, $writer->writes);
        $this->assertSame(3, $writer->writes[0][1]);
        $this->assertStringEndsWith('_000.log', $writer->writes[0][2]);
        $this->assertStringContainsString('message with tab\\tand newline\\n', $writer->writes[0][0]);
        $this->assertSame(['fallback message', 0, null], $writer->writes[1]);
    }
}

final class ErrorLoggerWriterDouble
{
    public array $writes = [];

    public function write(string $message, int $messageType = 0, ?string $destination = null): bool
    {
        $this->writes[] = [$message, $messageType, $destination];

        return true;
    }
}

final class ErrorLoggerFileStorageDouble
{
    public array $directories = [];

    public array $writable = [];

    public array $existing = [];

    public function isDirectory(string $path): bool
    {
        return $this->directories[$path] ?? false;
    }

    public function isWritable(string $path): bool
    {
        return $this->writable[$path] ?? true;
    }

    public function exists(string $path): bool
    {
        return $this->existing[$path] ?? false;
    }
}

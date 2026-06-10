<?php

declare(strict_types=1);

use fan\core\plain\db_file;
use fan\core\service\plain_file_context;
use FanTest\core\SourceFileContractTestCase;
use fan\core\service\config\row;


class PlainDbFileTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/plain/db_file.php';

    public function testKeyAndConfigAreStoredOnlyOnce(): void
    {
        $file = new PlainDbFileProbe('download');
        $config = new row(['first' => true]);
        $nextConfig = new row(['first' => false]);

        $this->assertSame('download', $file->getKey());
        $this->assertSame($file, $file->setConfig($config));
        $this->assertSame($file, $file->setConfig($nextConfig));
        $this->assertSame($config, $file->config());
    }

    public function testGetContentReturnsPlainContentBeforeOutputCallback(): void
    {
        $file = new PlainDbFileProbe('download');

        $this->assertSame([$file, 'outputContent'], $file->content());

        $file->setPlainContent('inline-content');

        $this->assertSame('inline-content', $file->content());
    }

    public function testOutputContentReadsFilePathOrPrintsErrorFallback(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'php-fan-db-file-');
        $this->assertIsString($filePath);
        file_put_contents($filePath, 'file-body');

        try {
            $file = new PlainDbFileProbe('download', new plain_file_context(fileStorage: new PlainDbFileStorageDouble()));
            $file->setFilePath($filePath);

            ob_start();
            $file->outputContent();
            $this->assertSame('file-body', ob_get_clean());

            $empty = new PlainDbFileProbe('download');
            ob_start();
            $empty->outputContent();
            $this->assertSame('Error file source', ob_get_clean());
        } finally {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }
    }

    public function testPlainFatalExceptionHelperDelegatesToContextFactory(): void
    {
        $context = new PlainDbFileContextDouble();
        $file = new PlainDbFileProbe('download', $context);

        $this->assertSame('plain problem', $file->plainFatal('plain problem')->getMessage());
        $this->assertSame([
            [$file, 'plain problem', E_USER_ERROR, null],
        ], $context->fatalCalls);
    }

    public function testSourceUsesInjectedPlainFileStorageForOutputAndMetadata(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('protected function createPlainFatalException(', $source);
        $this->assertStringContainsString('$this->context()->createPlainFatalException($this, $message, $code, $previous)', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->rewindStream($this->streamId)', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->passThroughStream($this->streamId)', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->outputFile($this->filePath)', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->isReadable($data[\'filePath\'])', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->modifiedTime($data[\'filePath\'])', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->size($data[\'filePath\'])', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_readable|filesize|filemtime|readfile|rewind|fpassthru)\s*\(/',
            $source
        );
    }
}

final class PlainDbFileProbe extends db_file
{
    public function __construct(string $key, ?object $context = null)
    {
        $this->key = $key;
        $this->context = $context;
    }

    public function config(): ?object
    {
        return $this->config;
    }

    public function setPlainContent(?string $content): void
    {
        $this->plainContent = $content;
    }

    public function setFilePath(?string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function content(): array|string
    {
        return $this->_getContent();
    }

    public function plainFatal(string $message): Throwable
    {
        return $this->createPlainFatalException($message);
    }
}

final class PlainDbFileStorageDouble
{
    public function outputFile(string $path): int|false
    {
        return readfile($path);
    }
}

final class PlainDbFileContextDouble
{
    public array $fatalCalls = [];

    public function createPlainFatalException(object $controller, string $message, int $code = E_USER_ERROR, ?Throwable $previous = null): Throwable
    {
        $this->fatalCalls[] = [$controller, $message, $code, $previous];

        return new RuntimeException($message, $code, $previous);
    }
}

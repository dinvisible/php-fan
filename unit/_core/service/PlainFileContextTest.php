<?php

declare(strict_types=1);

use fan\core\service\plain_file_context;
use FanTest\_core\SourceFileContractTestCase;

final class ServicePlainFileContextTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/plain_file_context.php';

    public function testDelegatesPlainFileDependencies(): void
    {
        $request = new stdClass();
        $application = new ServicePlainFileContextApplicationDouble();
        $databaseConnections = new ServicePlainFileContextDatabaseConnectionsDouble();
        $translation = new ServicePlainFileContextTranslationDouble();
        $runtime = new ServicePlainFileContextRuntimeDouble();
        $row = new stdClass();
        $entity = new ServicePlainFileContextEntityDouble($row);
        $cache = new stdClass();
        $imageModify = new stdClass();
        $imageMetadataReader = new stdClass();
        $fileStorage = new stdClass();
        $exceptionCalls = [];

        $context = new plain_file_context(
            $request,
            $application,
            $databaseConnections,
            $translation,
            static fn(string $type): object => $cache,
            $runtime,
            $entity,
            static fn(string $sourcePath): object => $imageModify,
            $imageMetadataReader,
            $fileStorage,
            static function (
                string $exceptionClass,
                object $controller,
                string $message,
                int $code,
                ?Throwable $previous
            ) use (&$exceptionCalls): Throwable {
                $exceptionCalls[] = [$exceptionClass, $controller, $message, $code, $previous];

                return new RuntimeException($message, $code, $previous);
            }
        );

        $controller = new stdClass();

        $this->assertSame($request, $context->request());
        $context->setApplicationName('admin');
        $this->assertSame('admin', $application->appName);
        $context->closeDatabaseConnections();
        $this->assertTrue($databaseConnections->closed);
        $this->assertSame('Message: ERROR_KEY', $context->message('ERROR_KEY'));
        $this->assertSame($cache, $context->cache('file_store'));
        $this->assertSame('/resolved/file.txt', $context->parsePath('{TEMP}/file.txt'));
        $this->assertSame($row, $context->fileDataRow());
        $this->assertSame($imageModify, $context->imageModify('/source.png'));
        $this->assertSame($imageMetadataReader, $context->imageMetadataReader());
        $this->assertSame($fileStorage, $context->fileStorage());
        $this->assertSame('plain fatal', $context->createPlainFatalException($controller, 'plain fatal')->getMessage());
        $this->assertSame([
            ['\fan\project\exception\plain\fatal', $controller, 'plain fatal', E_USER_ERROR, null],
        ], $exceptionCalls);
    }

    public function testSourceUsesInjectedFactoriesDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('($this->cacheFactory)($type)', $source);
        $this->assertStringContainsString('($this->imageModifyFactory)($sourcePath)', $source);
        $this->assertStringContainsString('private ?object $imageMetadataReader = null', $source);
        $this->assertStringContainsString('private ?object $fileStorage = null', $source);
        $this->assertStringContainsString('private mixed $plainExceptionFactory = null', $source);
        $this->assertStringContainsString('return $this->requireObject($this->fileStorage, \'Plain file storage\');', $source);
        $this->assertStringContainsString('public function createPlainFatalException(', $source);
        $this->assertStringContainsString('($this->plainExceptionFactory)(', $source);
        $this->assertStringNotContainsString('call_user_func', $source);
    }
}

final class ServicePlainFileContextApplicationDouble
{
    public ?string $appName = null;

    public function setAppName(string $appName): void
    {
        $this->appName = $appName;
    }
}

final class ServicePlainFileContextDatabaseConnectionsDouble
{
    public bool $closed = false;

    public function close(): void
    {
        $this->closed = true;
    }
}

final class ServicePlainFileContextTranslationDouble
{
    public function getMessage(string $key): string
    {
        return 'Message: ' . $key;
    }
}

final class ServicePlainFileContextRuntimeDouble
{
    public function parsePath(string $path): string
    {
        return str_replace('{TEMP}', '/resolved', $path);
    }
}

final class ServicePlainFileContextEntityDouble
{
    public function __construct(private object $row)
    {
    }

    public function getFileNsSuffix(): string
    {
        return 'ns_';
    }

    public function get(string $entityName): object
    {
        return new class($this->row, $entityName) {
            public function __construct(private object $row, public string $entityName)
            {
            }

            public function getNewRow(): object
            {
                return $this->row;
            }
        };
    }
}

<?php

declare(strict_types=1);

use fan\core\adapter\safe_serializer;
use fan\core\service\cache;
use fan\core\service\cache\file as FileCacheEngine;
use FanTest\core\SourceFileContractTestCase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use fan\core\adapter\cache_file_storage;
use fan\core\adapter\warning_capture;
use fan\core\base\service;
use fan\core\service\cache\base;


if (!class_exists('bootstrap', false)) {
    class bootstrap
    {
        public static function parsePath(string $path): string
        {
            return $path;
        }
    }
}

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ServiceCacheFileTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/cache/file.php';

    private array $tempDirs = [];

    protected function tearDown(): void
    {
        foreach ($this->tempDirs as $dir) {
            $this->removeDirectory($dir);
        }
        $this->tempDirs = [];
    }

    public function testStringPayloadIsSavedAsRawDataWithJsonMetaAndLoadedBack(): void
    {
        $dir = $this->makeTempDir();
        $engine = $this->engine('homepage', $dir);

        $engine->set('cached html', true);

        $dataFile = $dir . '/homepage.cache';
        $metaFile = $dir . '/homepage.meta';
        $this->assertFileExists($dataFile);
        $this->assertFileExists($metaFile);
        $this->assertSame('cached html', file_get_contents($dataFile));

        $meta = safe_serializer::decodeExternalPayload(
            (string)file_get_contents($metaFile),
            null,
            null,
            false,
            new warning_capture()
        );
        $this->assertSame('string', $meta['data_type']);
        $this->assertSame(300, $meta['lifetime']);

        $loaded = $this->engine('homepage', $dir);
        $this->assertSame('cached html', $loaded->get('fallback'));
    }

    public function testStructuredPayloadIsSavedAsJsonAndLoadedBack(): void
    {
        $dir = $this->makeTempDir();
        $engine = $this->engine('menu', $dir);

        $engine->set(['items' => ['home', 'about'], 'active' => true], true);

        $data = (string)file_get_contents($dir . '/menu.cache');
        $this->assertStringStartsWith(safe_serializer::JSON_PREFIX, $data);
        $this->assertSame(
            ['items' => ['home', 'about'], 'active' => true],
            safe_serializer::decodeExternalPayload(
                $data,
                null,
                null,
                false,
                new warning_capture()
            )
        );

        $loaded = $this->engine('menu', $dir);
        $this->assertSame(['items' => ['home', 'about'], 'active' => true], $loaded->get());
    }

    public function testExtraPathCreatesNestedCacheDirectory(): void
    {
        $dir = $this->makeTempDir();
        $engine = $this->engine('fragment', $dir);

        $engine->setExtraPath('compiled/templates')->set('body', true);

        $this->assertFileExists($dir . '/compiled/templates/fragment.cache');
        $this->assertFileExists($dir . '/compiled/templates/fragment.meta');
    }

    public function testDeleteRemovesCacheAndMetaFiles(): void
    {
        $dir = $this->makeTempDir();
        $engine = $this->engine('page', $dir);
        $engine->set(['value' => 1], true);

        $this->assertFileExists($dir . '/page.cache');
        $this->assertFileExists($dir . '/page.meta');

        $engine->delete();

        $this->assertFileDoesNotExist($dir . '/page.cache');
        $this->assertFileDoesNotExist($dir . '/page.meta');
        $this->assertSame('fallback', $engine->get('fallback'));
    }

    public function testCodeFileNameHashesUnsafeKeys(): void
    {
        $dir = $this->makeTempDir();
        $engine = $this->engine('unsafe/key', $dir, ['CODE_FILE_NAME' => true, 'FILE_EXT' => 'bin']);

        $engine->set('payload', true);

        $fileName = md5('unsafe/key');
        $this->assertFileExists($dir . '/' . $fileName . '.bin');
        $this->assertFileExists($dir . '/' . $fileName . '.meta');
    }

    public function testUnsafeCacheKeyUsesInjectedFacadeExceptionFactory(): void
    {
        $dir = $this->makeTempDir();
        $factoryCalls = [];
        $facade = new ServiceCacheFileFacadeDouble();
        $facade->setServiceDependencies(
            serviceExceptionFactory: static function (
                string $exceptionClass,
                service $service,
                string $message,
                int $code,
                ?Throwable $previous = null
            ) use (&$factoryCalls): Throwable {
                $factoryCalls[] = [$exceptionClass, $service, $message, $code, $previous];

                return new RuntimeException($message, $code, $previous);
            }
        );

        $engine = $this->engineWithFacade('unsafe/key', $dir, $facade);

        try {
            $engine->set('payload', true);
            $this->fail('Expected cache file engine to use injected facade exception factory.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Cache key "unsafe/key" can\'t be used for name of cache file.', $exception->getMessage());
            $savedProperty = new ReflectionProperty(base::class, 'saved');
            $savedProperty->setValue($engine, true);
        }

        $this->assertCount(1, $factoryCalls);
        $this->assertSame('\fan\project\exception\service\fatal', $factoryCalls[0][0]);
        $this->assertSame($facade, $factoryCalls[0][1]);
        $this->assertSame(E_USER_ERROR, $factoryCalls[0][3]);
        $this->assertNull($factoryCalls[0][4]);
    }

    public function testSourceNoLongerUsesFacadeAsServiceLocator(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('private ?object $fileStorage = null;', $source);
        $this->assertStringContainsString('$this->fileStorage()', $source);
        $this->assertStringContainsString('$this->createCacheFatalException(', $source);
        $this->assertStringNotContainsString('getContainerService(', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:file_exists|is_dir|is_writable|is_file|mkdir|file_get_contents|file_put_contents|unlink)\s*\(/',
            $source
        );
    }

    private function engine(string $key, string $dir, array $config = []): FileCacheEngine
    {
        return $this->engineWithFacade($key, $dir, new ServiceCacheFileFacadeDouble(), $config);
    }

    private function engineWithFacade(string $key, string $dir, ServiceCacheFileFacadeDouble $facade, array $config = []): FileCacheEngine
    {
        return new FileCacheEngine(
            $facade,
            'file_store',
            $key,
            $config + [
                'BASE_DIR' => $dir,
                'FILE_EXT' => 'cache',
                'LIFETIME' => 300,
            ],
            new ServiceCacheFileErrorLoggerDouble(),
            new ServiceCacheFileRuntimeDouble(),
            static fn(mixed $value): string => safe_serializer::encodeJson($value),
            static fn(
                string $payload,
                mixed $default = null,
                ?callable $onError = null,
                bool $returnOriginalOnLegacyFailure = false
            ): mixed => safe_serializer::decodeExternalPayload(
                $payload,
                $default,
                $onError,
                $returnOriginalOnLegacyFailure,
                new warning_capture()
            ),
            static fn(string $payload): bool => safe_serializer::isJsonPayload($payload),
            new cache_file_storage()
        );
    }

    private function makeTempDir(): string
    {
        $dir = sys_get_temp_dir() . '/php-fan-cache-file-' . uniqid('', true);
        mkdir($dir);
        $this->tempDirs[] = $dir;

        return $dir;
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }
}

final class ServiceCacheFileFacadeDouble extends cache
{
    public function __construct()
    {
    }

    public function getExceptionLogType(): string
    {
        return 'nothing';
    }
}

final class ServiceCacheFileRuntimeDouble
{
    public function parsePath(string $path): string
    {
        return $path;
    }
}

final class ServiceCacheFileErrorLoggerDouble
{
    public function logErrorMessage(
        string $message,
        string $title = '',
        string $note = '',
        bool $fixPosition = false,
        bool $allowDebug = true,
    ): void {
    }
}

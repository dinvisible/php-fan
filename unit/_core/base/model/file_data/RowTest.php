<?php

declare(strict_types=1);

use fan\core\base\model\entity;
use fan\core\base\model\file_data\row;
use FanTest\_core\SourceFileContractTestCase;

if (!class_exists('bootstrap', false)) {
    class bootstrap
    {
        public static array $log = [];

        private static ?object $initializer = null;

        public static function parsePath(string $path): string
        {
            return $path;
        }

        public static function getInitializer(): object
        {
            if (self::$initializer === null) {
                self::$initializer = new class {
                    public array $serviceParams = [];

                    public function setServiceParam($class): static
                    {
                        $this->serviceParams[] = $class;

                        return $this;
                    }
                };
            }

            return self::$initializer;
        }

        public static function logError($message): void
        {
            self::$log[] = $message;
        }
    }
}

class BaseModelFileDataRowTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/base/model/file_data/row.php';

    public function testGetFilePathBuildsTriadPathWithConnectionAndSafeExtension(): void
    {
        $row = new BaseModelFileDataRowProbe(
            id: 12345,
            config: [
                'file_store' => '/store/',
                'file_ext' => 'bin',
                'path_with_connection' => true,
            ],
            srcName: 'payload.php',
        );

        $this->assertSame('/store/main/012/345.bin', $row->getFilePath());
        $this->assertSame('/store/main/009/999.txt', $row->getFilePath(9999, srcName: 'readme.txt'));
        $this->assertSame(['/store/main/012/345', '/store/main/009/999'], $row->runtime->parsedPaths);
    }

    public function testGetFilePathRespectsAccessibilityFlagsWhenCheckingCurrentRow(): void
    {
        $row = new BaseModelFileDataRowProbe(
            id: 5,
            config: ['file_store' => '/store/', 'file_ext' => 'dat'],
            isAccessible: false,
        );

        $this->assertNull($row->getFilePath());
        $this->assertSame('/store/005.dat', $row->getFilePath(5));
    }

    public function testSetAllowLoadInfoKeepsRuntimeInfoLoadingDisabled(): void
    {
        $row = new BaseModelFileDataRowProbe();

        $row->setAllowLoadInfo(true);

        $property = new ReflectionProperty(row::class, 'loadInfo');
        $this->assertFalse($property->getValue($row));
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('private mixed $phpArrayFileLoader = null;', $source);
        $this->assertStringContainsString('private ?object $fileDataStorage = null;', $source);
        $this->assertStringContainsString('$this->loadPhpArrayFile($path, [])', $source);
        $this->assertStringContainsString('parent::setDependenciesFromEntityService($entity);', $source);
        $this->assertStringContainsString('$this->createModelRowFatalException(', $source);
        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\model\entity\fatal as fatalException;', $source);
        $this->assertStringNotContainsString('php_array_file::load', $source);
        $this->assertStringNotContainsString('include($path)', $source);
        $this->assertDoesNotMatchRegularExpression('/(?<!->)(?<!::)(?<!\\\\)\b(?:file_exists|is_link|readlink|is_dir|is_writable|is_file|mkdir|filesize|filemtime|move_uploaded_file|file_put_contents|clearstatcache|rename|copy|unlink)\s*\(/', $source);
    }

    public function testCheckCreatedDirUsesInjectedModelRowExceptionFactory(): void
    {
        $base = sys_get_temp_dir() . '/fan_file_data_row_blocker_' . bin2hex(random_bytes(4));
        file_put_contents($base, 'not a directory');

        $factoryCalls = [];
        $row = new BaseModelFileDataRowProbe(
            modelRowExceptionFactory: static function (
                string $exceptionClass,
                entity $entity,
                string $message,
                int $code,
                ?Throwable $previous = null
            ) use (&$factoryCalls): Throwable {
                $factoryCalls[] = [$exceptionClass, $entity, $message, $code, $previous];

                return new RuntimeException($message, $code, $previous);
            }
        );

        try {
            $row->checkCreatedDir($base . '/payload.bin');
            $this->fail('Expected injected exception factory to provide the thrown exception.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'It is inpossible create directory: ' . $base . ', because it is file there.',
                $exception->getMessage()
            );
        } finally {
            if (file_exists($base)) {
                unlink($base);
            }
        }

        $this->assertCount(1, $factoryCalls);
        $this->assertSame('\fan\project\exception\model\entity\fatal', $factoryCalls[0][0]);
        $this->assertSame($row->getEntity(), $factoryCalls[0][1]);
        $this->assertSame(E_USER_ERROR, $factoryCalls[0][3]);
        $this->assertNull($factoryCalls[0][4]);
    }

    public function testLoadByIdUsesInjectedPhpArrayLoaderForInfoFile(): void
    {
        $dir = sys_get_temp_dir() . '/fan_file_data_row_' . bin2hex(random_bytes(4));
        mkdir($dir, 0777, true);
        $infoFile = $dir . '/info.php';
        $storedFile = $dir . '/stored.bin';
        file_put_contents($infoFile, "<?php\nreturn [];\n");
        file_put_contents($storedFile, 'payload');

        $loaderCalls = [];
        $loadedRow = [
            'id_file_data' => 17,
            'src_name' => 'stored.bin',
        ];

        $row = new BaseModelFileDataRowProbe(
            infoPathOverride: $infoFile,
            resolvedFilePath: $storedFile,
            phpArrayFileLoader: static function (string $path, mixed $default = null) use (&$loaderCalls, $loadedRow): array {
                $loaderCalls[] = [$path, $default];

                return $loadedRow;
            }
        );
        $row->enableInfoLoading();

        try {
            $this->assertTrue($row->loadById(17));
            $this->assertSame([[$infoFile, []]], $loaderCalls);
            $this->assertSame($loadedRow, $row->mainProperty);
        } finally {
            if (file_exists($storedFile)) {
                unlink($storedFile);
            }
            if (file_exists($infoFile)) {
                unlink($infoFile);
            }
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }
}

final class BaseModelFileDataRowProbe extends row
{
    public BaseModelFileDataRowRuntimeDouble $runtime;
    public BaseModelFileDataRowStorageDouble $storage;
    public ?array $mainProperty = null;

    public function __construct(
        private mixed $id = 1,
        private array $config = [],
        private string $srcName = 'file.dat',
        private bool $isAccessible = true,
        private bool $isDeleted = false,
        private ?string $infoPathOverride = null,
        private ?string $resolvedFilePath = null,
        ?callable $phpArrayFileLoader = null,
        ?BaseModelFileDataRowStorageDouble $storage = null,
        ?callable $modelRowExceptionFactory = null,
    ) {
        $this->entity = new BaseModelFileDataRowEntityDouble();
        $this->runtime = new BaseModelFileDataRowRuntimeDouble();
        $this->storage = $storage ?? new BaseModelFileDataRowStorageDouble();
        if ($modelRowExceptionFactory !== null) {
            $this->setRowDependencies(modelRowExceptionFactory: $modelRowExceptionFactory);
        }
        $this->setFileDataRowDependencies(
            $this->runtime,
            phpArrayFileLoader: $phpArrayFileLoader,
            fileDataStorage: $this->storage
        );
    }

    public function enableInfoLoading(): void
    {
        $this->loadInfo = true;
    }

    public function setMainProperty(array $row): void
    {
        $this->mainProperty = $row;
    }

    protected function getInfoPath(mixed $idVal): string
    {
        return $this->infoPathOverride ?? parent::getInfoPath($idVal);
    }

    public function getFilePath(?int $id = null, bool $checkAddCondition = true, string|int|float|null $srcName = ''): ?string
    {
        return $this->resolvedFilePath ?? parent::getFilePath($id, $checkAddCondition, $srcName);
    }

    public function getId(bool $allowException = true, bool $useSourceValue = false, bool $alwaysArray = false): mixed
    {
        return $alwaysArray ? ['id_file_data' => $this->id] : $this->id;
    }

    public function getConfig(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->config : ($this->config[$key] ?? $default);
    }

    public function get_is_accessible(mixed $defaultVal = null, bool $allowException = true): mixed
    {
        return $this->isAccessible;
    }

    public function get_is_deleted(mixed $defaultVal = null, bool $allowException = true): mixed
    {
        return $this->isDeleted;
    }

    public function get_src_name(mixed $srcName = '', bool $allowException = true): mixed
    {
        return $srcName ?: $this->srcName;
    }
}

final class BaseModelFileDataRowEntityDouble extends entity
{
    private BaseModelFileDataRowConnectionDouble $connectionDouble;

    public function __construct()
    {
        $this->connectionDouble = new BaseModelFileDataRowConnectionDouble();
    }

    public function getConnection(): object
    {
        return $this->connectionDouble;
    }
}

final class BaseModelFileDataRowRuntimeDouble
{
    public array $parsedPaths = [];

    public function parsePath(string $path): string
    {
        $this->parsedPaths[] = $path;

        return $path;
    }
}

final class BaseModelFileDataRowStorageDouble
{
    public array $deletedPaths = [];

    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function isLink(string $path): bool
    {
        return is_link($path);
    }

    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    public function readLink(string $path): string|false
    {
        return readlink($path);
    }

    public function makeDirectory(string $path): bool
    {
        return mkdir($path);
    }

    public function size(string $path): int|false
    {
        return filesize($path);
    }

    public function modifiedTime(string $path): int|false
    {
        return filemtime($path);
    }

    public function moveUploadedFile(string $sourcePath, string $targetPath): bool
    {
        return false;
    }

    public function write(string $path, string $data): int|false
    {
        return file_put_contents($path, $data);
    }

    public function rename(string $sourcePath, string $targetPath): bool
    {
        return rename($sourcePath, $targetPath);
    }

    public function copy(string $sourcePath, string $targetPath): bool
    {
        return copy($sourcePath, $targetPath);
    }

    public function delete(string $path): bool
    {
        $this->deletedPaths[] = $path;

        return unlink($path);
    }

    public function clearStatCache(): void
    {
        clearstatcache();
    }
}

final class BaseModelFileDataRowConnectionDouble
{
    public function __construct()
    {
    }

    public function getConnectionName(): ?string
    {
        return 'main';
    }
}

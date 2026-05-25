<?php

declare(strict_types=1);

use fan\core\base\model\file_data\row;
use fan\core\service\cache;
use fan\core\service\cache\wrapper\file_data;
use FanTest\_core\SourceFileContractTestCase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

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
class ServiceCacheWrapperFileDataTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/cache/wrapper/file_data.php';

    protected function tearDown(): void
    {
    }

    public function testGetFileDataReturnsCachedPayloadWithoutResettingRow(): void
    {
        $row = new FileDataWrapperRowDouble(id: 42);
        $cache = new FileDataWrapperCacheDouble([
            'filePath' => '/tmp/cached.txt',
            'rowData' => ['id_file_data' => 42],
        ]);

        $wrapper = $this->wrapper($row, $cache);

        $this->assertSame([
            'filePath' => '/tmp/cached.txt',
            'rowData' => ['id_file_data' => 42],
        ], $wrapper->getFileData());
        $this->assertSame([['42', null]], $cache->getCalls);
        $this->assertSame([], $cache->setCalls);
        $this->assertSame(0, $row->checkAccessCalls);
    }

    public function testResetRefusesToCacheRowsWithoutAccess(): void
    {
        $row = new FileDataWrapperRowDouble(id: 12, hasAccess: false);
        $cache = new FileDataWrapperCacheDouble();

        $wrapper = $this->wrapper($row, $cache);

        $this->assertFalse($wrapper->reset());
        $this->assertSame(1, $row->checkAccessCalls);
        $this->assertSame([], $cache->setCalls);
    }

    public function testGetFileDataRebuildsPayloadAndWritesItThroughCacheOnCacheMiss(): void
    {
        $filePath = '/tmp/php-fan-file-data-payload.txt';
        $rowData = [
            'id_file_data' => 17,
            'src_name' => 'payload.txt',
        ];
        $row = new FileDataWrapperRowDouble(
            id: 17,
            filePath: $filePath,
            rowData: $rowData,
        );
        $cache = new FileDataWrapperCacheDouble();
        $metadata = new FileDataWrapperFileMetadataDouble(1234567890);
        $wrapper = $this->wrapper($row, $cache, fileMetadata: $metadata);

        $expectedPayload = [
            'filePath' => $filePath,
            'fileDate' => 1234567890,
            'rowData' => $rowData,
        ];
        $this->assertSame($expectedPayload, $wrapper->getFileData());
        $this->assertSame([['17', null]], $cache->getCalls);
        $this->assertSame([['17', $expectedPayload, true]], $cache->setCalls);
        $this->assertSame([$filePath], $metadata->modifiedTimeCalls);
    }

    public function testLazyRowLookupRetriesWithEncryptedIdWhenEncryptionModeIsNotSpecified(): void
    {
        $row = new FileDataWrapperRowDouble(id: 99, isLoaded: false);
        $row->loadResults = [
            false => false,
            true => true,
        ];
        $entityService = new FileDataWrapperEntityServiceDouble($row, 'media_');
        $wrapper = $this->wrapper(99, new FileDataWrapperCacheDouble(), $entityService);

        $this->assertSame($row, $wrapper->exposedRow());
        $this->assertSame('media_file_data', $entityService->repository->entityName);
        $this->assertSame([
            [99, false],
            [99, true],
        ], $row->loadByIdCalls);
    }

    public function testLazyRowLookupUsesExplicitEncryptionModeWithoutRetry(): void
    {
        $row = new FileDataWrapperRowDouble(id: 88, isLoaded: false);
        $entityService = new FileDataWrapperEntityServiceDouble($row);
        $wrapper = $this->wrapper(88, new FileDataWrapperCacheDouble(), $entityService, true);

        $this->assertSame($row, $wrapper->exposedRow());
        $this->assertSame([[88, true]], $row->loadByIdCalls);
    }

    public function testConstructorRejectsInvalidRowDataThroughTypedSignature(): void
    {
        $this->expectException(TypeError::class);

        new FileDataWrapperProbe(
            'invalid-row-data',
            null,
            static fn(string $type): FileDataWrapperCacheDouble => new FileDataWrapperCacheDouble(),
            static fn(): object => new stdClass(),
            new FileDataWrapperRuntimeDouble(),
            new FileDataWrapperFileMetadataDouble()
        );
    }

    private function wrapper(
        int|row $rowData,
        FileDataWrapperCacheDouble $cache,
        ?object $entityService = null,
        ?bool $idIsEncrypt = null,
        ?object $fileMetadata = null,
    ): FileDataWrapperProbe {
        return new FileDataWrapperProbe(
            $rowData,
            $idIsEncrypt,
            static fn(string $type): FileDataWrapperCacheDouble => $cache,
            static fn(): ?object => $entityService,
            new FileDataWrapperRuntimeDouble(),
            $fileMetadata ?? new FileDataWrapperFileMetadataDouble()
        );
    }

    public function testSourceNoLongerUsesContainerServiceLocator(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('container_aware_trait', $source);
    }

    public function testSourceNoLongerConstructsProjectError500Directly(): void
    {
        $this->assertStringNotContainsString('new \fan\project\exception\error500', $this->sourceCode());
    }

    public function testSourceNoLongerReadsFileMetadataDirectly(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:filemtime|filesize)\s*\(/',
            $this->sourceCode()
        );
    }
}

final class FileDataWrapperProbe extends file_data
{
    public function exposedRow(): ?row
    {
        return $this->_getRow();
    }
}

final class FileDataWrapperRuntimeDouble
{
    public function parsePath(string $path): string
    {
        return $path;
    }
}

final class FileDataWrapperFileMetadataDouble
{
    public array $modifiedTimeCalls = [];

    public function __construct(private int|false $modifiedTime = 1)
    {
    }

    public function modifiedTime(string $path): int|false
    {
        $this->modifiedTimeCalls[] = $path;

        return $this->modifiedTime;
    }
}

final class FileDataWrapperCacheDouble extends cache
{
    public array $getCalls = [];

    public array $setCalls = [];

    public function __construct(private mixed $cachedValue = null)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->getCalls[] = [$key, $default];

        return $this->cachedValue ?? $default;
    }

    public function set(string $key, mixed $value, bool $autoSave = true): static
    {
        $this->setCalls[] = [$key, $value, $autoSave];
        $this->cachedValue = $value;

        return $this;
    }
}

final class FileDataWrapperRowDouble extends row
{
    public int $checkAccessCalls = 0;

    public array $loadByIdCalls = [];

    public array $loadResults = [];

    public function __construct(
        private mixed $id = 1,
        private bool $isLoaded = true,
        private bool $hasAccess = true,
        private ?string $filePath = null,
        private array $rowData = [],
    ) {
    }

    public function getId(bool $allowException = true, bool $useSourceValue = false, bool $alwaysArray = false): mixed
    {
        return $alwaysArray ? ['id_file_data' => $this->id] : $this->id;
    }

    public function checkIsLoad(): bool
    {
        return $this->isLoaded;
    }

    public function loadById(mixed $idVal = null, bool $idIsEncrypt = false): mixed
    {
        $this->loadByIdCalls[] = [$idVal, $idIsEncrypt];
        $this->isLoaded = $this->loadResults[$idIsEncrypt] ?? true;

        return $this;
    }

    public function checkAccess(): bool
    {
        $this->checkAccessCalls++;

        return $this->hasAccess;
    }

    public function getFilePath(?int $id = null, bool $checkAddCondition = true, string|int|float|null $srcName = ''): ?string
    {
        return $this->filePath;
    }

    public function toArray(): array
    {
        return $this->rowData;
    }
}

final class FileDataWrapperEntityServiceDouble
{
    public FileDataWrapperEntityRepositoryDouble $repository;

    public function __construct(FileDataWrapperRowDouble $row, private string $fileNsSuffix = '')
    {
        $this->repository = new FileDataWrapperEntityRepositoryDouble($row);
    }

    public function getFileNsSuffix(): string
    {
        return $this->fileNsSuffix;
    }

    public function get(string $entityName, array $param = []): FileDataWrapperEntityRepositoryDouble
    {
        $this->repository->entityName = $entityName;
        $this->repository->param = $param;

        return $this->repository;
    }
}

final class FileDataWrapperEntityRepositoryDouble
{
    public ?string $entityName = null;

    public array $param = [];

    public array $getRowByIdCalls = [];

    public function __construct(private FileDataWrapperRowDouble $row)
    {
    }

    public function getRowById(mixed $rowId = null, bool $idIsEncrypt = false): FileDataWrapperRowDouble
    {
        $this->getRowByIdCalls[] = [$rowId, $idIsEncrypt];

        return $this->row;
    }

    public function getNewRow(): FileDataWrapperRowDouble
    {
        return $this->row;
    }
}

<?php

declare(strict_types=1);

use fan\core\base\model\entity;
use fan\core\base\model\file_data\row as FileDataRow;
use fan\core\base\model\spec_file\image\row;
use fan\core\base\model\spec_file\image\row_state;
use FanTest\core\SourceFileContractTestCase;

class BaseModelSpecFileImageRowTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/base/model/spec_file/image/row.php';

    public function testGetImageDataCombinesImageFieldsAndEntityFileMetadata(): void
    {
        $row = new BaseModelSpecFileImageRowProbe(
            fields: ['id_image' => 9, 'width' => 640, 'height' => 480],
            file: new BaseModelSpecFileImageRowFileDouble(),
        );

        $this->assertSame([
            'width' => 640,
            'height' => 480,
            'id' => 9,
            'description' => 'Preview',
            'src_name' => 'preview.jpg',
        ], $row->getImageData());
    }

    public function testResizeImagePreservesAspectRatioAndRewritesUrlTemplate(): void
    {
        $row = new BaseModelSpecFileImageRowProbe(width: 800, height: 400);
        $param = [
            'width' => 200,
            'height' => 200,
            'url_suffix' => '&w={width}&h={height}',
            'full_url' => '/nail.php?id=9&w={width}&h={height}',
        ];

        $row->exposedResizeImage($param);

        $this->assertSame(200, $param['width']);
        $this->assertSame(100.0, $param['height']);
        $this->assertSame('&w=200&h=100', $param['url_suffix']);
        $this->assertSame('/nail.php?id=9&w=200&h=100', $param['full_url']);
    }

    public function testInvalidAdvancedImageTypeReturnsNull(): void
    {
        $row = new BaseModelSpecFileImageRowProbe(isLoaded: true);

        $this->assertNull($row->advGetImgTag('unknown', []));
    }

    public function testGetImgTagFailsExplicitlyAfterTemplateServiceRemoval(): void
    {
        $runtime = new BaseModelSpecFileImageRowRuntimeDouble();
        $state = new row_state();
        $row = new BaseModelSpecFileImageRowProbe(width: 320, height: 240);
        $row->setSpecFileImageRowDependencies(
            $runtime,
            null,
            null,
            null,
            $state
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Template service has been removed for spec image row.');

        $row->getImgTag(['img' => []]);
    }

    public function testImageTemplateCacheUsesInjectedStateInsteadOfStaticProperty(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('Spec-file image row state is not configured for spec-file image row.', $source);
        $this->assertStringNotContainsString('new row_state()', $source);
        $this->assertStringNotContainsString('private static array $template', $source);
        $this->assertStringNotContainsString('self::$template', $source);

        $runtime = new BaseModelSpecFileImageRowRuntimeDouble();
        $state = new row_state();
        $first = new BaseModelSpecFileImageRowProbe(width: 320, height: 240, entityName: 'shared_image');
        $second = new BaseModelSpecFileImageRowProbe(width: 640, height: 480, entityName: 'shared_image');
        foreach ([$first, $second] as $row) {
            $row->setSpecFileImageRowDependencies(
                $runtime,
                null,
                null,
                null,
                $state
            );
        }

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Template service has been removed for spec image row.');

        $first->getImgTag(['img' => []]);
    }

    public function testImageTemplateFailsExplicitlyAfterTemplateServiceRemoval(): void
    {
        $row = new BaseModelSpecFileImageRowProbe(width: 320, height: 240);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Template service has been removed for spec image row.');

        $row->getImgTag(['img' => []]);
    }

    public function testRotateImageUsesInjectedImageModifyFactory(): void
    {
        $imageModify = new BaseModelSpecFileImageRowImageModifyDouble();
        $error = new BaseModelSpecFileImageRowErrorDouble();
        $row = new BaseModelSpecFileImageRowProbe(file: new BaseModelSpecFileImageRowFileDouble('/missing-image.jpg'));
        $row->setSpecFileImageRowDependencies(
            null,
            null,
            fn(string $sourcePath): BaseModelSpecFileImageRowImageModifyDouble => $imageModify->withSource($sourcePath),
            fn(): BaseModelSpecFileImageRowErrorDouble => $error,
            imageSourceFileStorage: new BaseModelSpecFileImageRowSourceFileStorageDouble(false)
        );

        $row->rotateImage(90, 0x000000, 1);

        $this->assertSame('/missing-image.jpg', $imageModify->sourcePath);
        $this->assertSame([[90, 0x000000, 1]], $imageModify->rotateCalls);
        $this->assertSame([null], $imageModify->saveAndReplaceCalls);
        $this->assertSame('Image file "/missing-image.jpg" is not readable.', $error->messages[0][0]);
    }

    public function testSaveImageUsesInjectedImageMetadataReader(): void
    {
        $imagePath = 'https://example.test/readable-image.jpg';
        $file = new BaseModelSpecFileImageRowFileDouble($imagePath, true);
        $metadataReader = new BaseModelSpecFileImageRowMetadataReaderDouble([320, 240, IMAGETYPE_JPEG, 'mime' => 'image/jpeg']);
        $row = new BaseModelSpecFileImageRowProbe(file: $file);
        $row->setSpecFileImageRowDependencies(imageMetadataReader: $metadataReader);

        $this->assertTrue($row->setLocalFile($imagePath, 'Description', 'Alt text', 'source.jpg'));
        $this->assertSame([$imagePath, $imagePath], $metadataReader->paths);
        $this->assertSame([[$imagePath, 'image', 'image/jpeg', 'Description', 'source.jpg', false]], $file->setLocalFileCalls);
        $this->assertSame(320, $row->savedWidth);
        $this->assertSame(240, $row->savedHeight);
        $this->assertSame(IMAGETYPE_JPEG, $row->savedImageType);
        $this->assertSame('Alt text', $row->savedAlt);
        $this->assertSame(1, $row->saveCalls);
    }

    public function testImageMetadataWarningIsLoggedThroughInjectedReader(): void
    {
        $error = new BaseModelSpecFileImageRowErrorDouble();
        $metadataReader = new BaseModelSpecFileImageRowMetadataReaderDouble(false, 'metadata warning');
        $row = new BaseModelSpecFileImageRowProbe(file: new BaseModelSpecFileImageRowFileDouble('https://example.test/readable-image.jpg', true));
        $row->setSpecFileImageRowDependencies(
            errorFactory: fn(): BaseModelSpecFileImageRowErrorDouble => $error,
            imageMetadataReader: $metadataReader
        );

        $this->assertFalse($row->setLocalFile('https://example.test/readable-image.jpg'));
        $this->assertSame('metadata warning', $error->messages[0][0]);
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('set_error_handler(', $source);
        $this->assertStringNotContainsString('restore_error_handler(', $source);
        $this->assertStringNotContainsString('getimagesize(', $source);
        $this->assertStringNotContainsString('image_metadata_reader.php', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\image_metadata_reader()', $source);
        $this->assertStringContainsString('private ?object $imageSourceFileStorage = null;', $source);
        $this->assertStringContainsString('$this->imageSourceFileStorage()->isFile($path)', $source);
        $this->assertStringContainsString('$this->imageSourceFileStorage()->isReadable($path)', $source);
        $this->assertStringContainsString("throw new \RuntimeException('Image metadata reader is not configured for spec-file image row.');", $source);
        $this->assertStringContainsString("throw new \RuntimeException('Image source file storage is not configured for spec-file image row.');", $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_file|is_readable)\s*\(/',
            $source
        );
    }

    public function testLocalImageReadabilityUsesInjectedSourceFileStorage(): void
    {
        $error = new BaseModelSpecFileImageRowErrorDouble();
        $metadataReader = new BaseModelSpecFileImageRowMetadataReaderDouble([320, 240, IMAGETYPE_JPEG, 'mime' => 'image/jpeg']);
        $sourceFileStorage = new BaseModelSpecFileImageRowSourceFileStorageDouble();
        $row = new BaseModelSpecFileImageRowProbe(file: new BaseModelSpecFileImageRowFileDouble('/tmp/local-image.jpg', true));
        $row->setSpecFileImageRowDependencies(
            errorFactory: fn(): BaseModelSpecFileImageRowErrorDouble => $error,
            imageMetadataReader: $metadataReader,
            imageSourceFileStorage: $sourceFileStorage
        );

        $this->assertTrue($row->setLocalFile('/tmp/local-image.jpg'));
        $this->assertSame(['/tmp/local-image.jpg', '/tmp/local-image.jpg'], $sourceFileStorage->isFilePaths);
        $this->assertSame(['/tmp/local-image.jpg', '/tmp/local-image.jpg'], $sourceFileStorage->readablePaths);
        $this->assertSame(['/tmp/local-image.jpg', '/tmp/local-image.jpg'], $metadataReader->paths);
        $this->assertSame([], $error->messages);
    }
}

final class BaseModelSpecFileImageRowProbe extends row
{
    public function __construct(
        private array $fields = ['id_image' => 9],
        private ?FileDataRow $file = null,
        private int|float $width = 640,
        private int|float $height = 480,
        private bool $isLoaded = true,
        ?string $entityName = null,
    ) {
        $this->entity = new BaseModelSpecFileImageRowEntityDouble($entityName);
        $this->file ??= new BaseModelSpecFileImageRowFileDouble();
    }

    public function getFields(mixed $keys = null, bool $allExists = true): array
    {
        return $this->fields;
    }

    public function getId(bool $allowException = true, bool $useSourceValue = false, bool $alwaysArray = false): mixed
    {
        return 9;
    }

    public function getEntityFile(): FileDataRow
    {
        return $this->file;
    }

    public function checkIsLoad(): bool
    {
        return $this->isLoaded;
    }

    public function get_width(mixed $defaultVal = null, bool $allowException = true): mixed
    {
        return $this->width;
    }

    public function get_height(mixed $defaultVal = null, bool $allowException = true): mixed
    {
        return $this->height;
    }

    public function get_alt(mixed $defaultVal = null, bool $allowException = true): mixed
    {
        return 'Alt';
    }

    public int|float|null $savedWidth = null;
    public int|float|null $savedHeight = null;
    public int|float|null $savedImageType = null;
    public ?string $savedAlt = null;
    public int $saveCalls = 0;

    public function set_width(mixed $val): static
    {
        $this->savedWidth = $val;

        return $this;
    }

    public function set_height(mixed $val): static
    {
        $this->savedHeight = $val;

        return $this;
    }

    public function set_img_type(mixed $val): static
    {
        $this->savedImageType = $val;

        return $this;
    }

    public function set_alt(mixed $val): static
    {
        $this->savedAlt = $val;

        return $this;
    }

    public function setId(mixed $idVal): void
    {
    }

    public function save(): static
    {
        $this->saveCalls++;

        return $this;
    }

    public function exposedResizeImage(array &$param): void
    {
        $this->resizeImage($param);
    }

    public function getConfig(?string $key = null, mixed $default = null): mixed
    {
        return $key === 'TEMPLATE_PATH' ? '{PROJECT}/data/special_templates/show_image.tpl' : $default;
    }
}

final class BaseModelSpecFileImageRowEntityDouble extends entity
{
    private BaseModelSpecFileImageRowDescriptionDouble $descriptionDouble;

    public function __construct(?string $name = null)
    {
        $this->name = $name ?? 'image_test_' . spl_object_id($this);
        $this->descriptionDouble = new BaseModelSpecFileImageRowDescriptionDouble();
    }

    public function getDescription(array $param = []): object
    {
        return $this->descriptionDouble;
    }
}

final class BaseModelSpecFileImageRowDescriptionDouble
{
    public function __construct()
    {
    }

    public function getPrimeryKey(): string
    {
        return 'id_image';
    }
}

final class BaseModelSpecFileImageRowFileDouble extends FileDataRow
{
    public array $setLocalFileCalls = [];

    public function __construct(private string $filePath = '', private bool $forceLocalReadable = false)
    {
    }

    public function get_description(mixed $defaultVal = null, bool $allowException = true): mixed
    {
        return 'Preview';
    }

    public function get_src_name(mixed $defaultVal = null, bool $allowException = true): mixed
    {
        return 'preview.jpg';
    }

    public function getFilePath(?int $id = null, bool $checkAddCondition = true, string|int|float|null $srcName = ''): ?string
    {
        return $this->filePath;
    }

    public function setLocalFile(
        string $srcPath,
        string $fileType = 'file',
        string $mimeType = 'application/octet-stream',
        string $decription = '',
        ?string $name = null,
        bool $deleteOrigin = false
    ): bool {
        $this->setLocalFileCalls[] = [$srcPath, $fileType, $mimeType, $decription, $name, $deleteOrigin];
        $this->filePath = $srcPath;

        return $this->forceLocalReadable;
    }

    public function getId(bool $allowException = true, bool $useSourceValue = false, bool $alwaysArray = false): mixed
    {
        return 9;
    }
}

final class BaseModelSpecFileImageRowMetadataReaderDouble
{
    public array $paths = [];

    public function __construct(private array|false $result, private ?string $warning = null)
    {
    }

    public function size(string $path, ?callable $onWarning = null): array|false
    {
        $this->paths[] = $path;
        if ($this->warning !== null && $onWarning !== null) {
            $onWarning($this->warning, E_USER_WARNING);
        }

        return $this->result;
    }
}

final class BaseModelSpecFileImageRowRuntimeDouble
{
    public array $parsedPaths = [];

    public function parsePath(string $path): string
    {
        $this->parsedPaths[] = $path;

        return $path;
    }
}

final class BaseModelSpecFileImageRowTemplateServiceDouble
{
    public array $getCalls = [];
    public BaseModelSpecFileImageRowTemplateDouble $template;

    public function __construct()
    {
        $this->template = new BaseModelSpecFileImageRowTemplateDouble();
    }

    public function get(string $templatePath, string $templateType): BaseModelSpecFileImageRowTemplateDouble
    {
        $this->getCalls[] = [$templatePath, $templateType];

        return $this->template;
    }
}

final class BaseModelSpecFileImageRowTemplateDouble
{
    public mixed $baseParam = null;
    public array $assigned = [];

    public function setBaseParam(mixed $param): void
    {
        $this->baseParam = $param;
    }

    public function assign(string $key, mixed $value): void
    {
        $this->assigned[$key] = $value;
    }

    public function fetch(): string
    {
        return 'rendered-image';
    }
}

final class BaseModelSpecFileImageRowImageModifyDouble
{
    public ?string $sourcePath = null;
    public array $rotateCalls = [];
    public array $saveAndReplaceCalls = [];

    public function withSource(string $sourcePath): self
    {
        $this->sourcePath = $sourcePath;

        return $this;
    }

    public function rotate(int|float $angle, int $bgrColor = 0xFFFFFF, int|float $fix = 0): void
    {
        $this->rotateCalls[] = [$angle, $bgrColor, $fix];
    }

    public function saveAndReplace(mixed $targetPath = null): void
    {
        $this->saveAndReplaceCalls[] = $targetPath;
    }
}

final class BaseModelSpecFileImageRowErrorDouble
{
    public array $messages = [];

    public function logErrorMessage(
        string $message,
        string $title,
        string $note = '',
        bool $isTrace = true,
        bool $displayError = true
    ): void {
        $this->messages[] = [$message, $title, $note, $isTrace, $displayError];
    }
}

final class BaseModelSpecFileImageRowSourceFileStorageDouble
{
    public array $isFilePaths = [];
    public array $readablePaths = [];

    public function __construct(private bool $readable = true)
    {
    }

    public function isFile(string $path): bool
    {
        $this->isFilePaths[] = $path;

        return $this->readable;
    }

    public function isReadable(string $path): bool
    {
        $this->readablePaths[] = $path;

        return $this->readable;
    }
}

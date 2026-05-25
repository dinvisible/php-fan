<?php

declare(strict_types=1);

use fan\core\base\model\entity;
use fan\core\base\model\file_data\row as FileDataRow;
use fan\core\base\model\spec_file\image\row as ImageRow;
use fan\core\block\admin\upload_image;
use FanTest\_core\SourceFileContractTestCase;

class BlockAdminUploadImageTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/block/admin/upload_image.php';

    public function testOperationSetAttributesUpdatesLoadedImageAndEntityFile(): void
    {
        $block = (new ReflectionClass(upload_image::class))->newInstanceWithoutConstructor();
        $image = new BlockAdminUploadImageRowDouble(true);
        $data = [
            'alt' => 'Alt text',
            'description' => 'Description',
            'op' => 'sa',
        ];

        $block->operationSetAttributes($data, $image);

        $this->assertSame([[['alt' => 'Alt text'], true]], $image->setFieldsCalls);
        $this->assertSame([[['description' => 'Description'], true]], $image->file->setFieldsCalls);
        $this->assertSame('sa', $data['op']);
    }

    public function testOperationSetAttributesClearsOperationWhenImageIsMissing(): void
    {
        $block = (new ReflectionClass(upload_image::class))->newInstanceWithoutConstructor();
        $image = new BlockAdminUploadImageRowDouble(false);
        $data = ['alt' => 'Alt text', 'description' => 'Description', 'op' => 'sa'];

        $block->operationSetAttributes($data, $image);

        $this->assertNull($data['op']);
        $this->assertSame([], $image->setFieldsCalls);
    }

    public function testSourceDoesNotUseContainerServiceLocator(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('ini_get(', $source);
        $this->assertStringNotContainsString('getimagesize(', $source);
        $this->assertStringContainsString('use upload_size_limit_provider_aware_trait;', $source);
        $this->assertStringContainsString('$this->uploadSizeLimit()', $source);
        $this->assertStringContainsString('$this->imageMetadataReader()->size(', $source);
    }
}

final class BlockAdminUploadImageRowDouble extends ImageRow
{
    public array $setFieldsCalls = [];

    public BlockAdminUploadImageFileRowDouble $file;

    public function __construct(private bool $loaded)
    {
        $this->file = new BlockAdminUploadImageFileRowDouble();
        $this->entity = new BlockAdminUploadImageEntityDouble();
    }

    public function checkIsLoad(): bool
    {
        return $this->loaded;
    }

    public function setFields(mixed $fields, bool $isSave = false): static
    {
        $this->setFieldsCalls[] = [$fields, $isSave];

        return $this;
    }

    public function getEntityFile(): FileDataRow
    {
        return $this->file;
    }
}

final class BlockAdminUploadImageFileRowDouble extends FileDataRow
{
    public array $setFieldsCalls = [];

    public function __construct()
    {
    }

    public function setFields(mixed $fields, bool $isSave = false): static
    {
        $this->setFieldsCalls[] = [$fields, $isSave];

        return $this;
    }
}

final class BlockAdminUploadImageEntityDouble extends entity
{
    private BlockAdminUploadImageConnectionDouble $connectionDouble;

    public function __construct()
    {
        $this->connectionDouble = new BlockAdminUploadImageConnectionDouble();
    }

    public function getConnection(): object
    {
        return $this->connectionDouble;
    }
}

final class BlockAdminUploadImageConnectionDouble
{
    public int $commitCalls = 0;

    public function __construct()
    {
    }

    public function commit(): static
    {
        $this->commitCalls++;

        return $this;
    }
}

<?php

declare(strict_types=1);

use fan\core\block\admin\upload_file;
use FanTest\_core\SourceFileContractTestCase;

class BlockAdminUploadFileTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/block/admin/upload_file.php';

    public function testGetFileDataReturnsNullForEmptyIdAndIdPayloadOtherwise(): void
    {
        $block = (new ReflectionClass(upload_file::class))->newInstanceWithoutConstructor();

        $this->assertNull($block->getFileData(null));
        $this->assertSame(['id' => 55], $block->getFileData(55));
    }

    public function testSourceDoesNotUseContainerServiceLocator(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('ini_get(', $source);
        $this->assertStringContainsString('use upload_size_limit_provider_aware_trait;', $source);
        $this->assertStringContainsString('$this->uploadSizeLimit()', $source);
    }

    public function testUploadSizeLimitComesFromInjectedProvider(): void
    {
        $block = (new ReflectionClass(BlockAdminUploadFileProbe::class))->newInstanceWithoutConstructor();

        $block->setUploadSizeLimitProvider(static fn(): string => '12M');

        $this->assertSame('12M', $block->exposedUploadSizeLimit());
    }

    public function testBlockDependenciesWireUploadSizeLimitProvider(): void
    {
        $block = (new ReflectionClass(BlockAdminUploadFileProbe::class))->newInstanceWithoutConstructor();

        $block->setBlockDependencies([
            'uploadSizeLimitProvider' => static fn(): string => '14M',
        ]);

        $this->assertSame('14M', $block->exposedUploadSizeLimit());
    }
}

final class BlockAdminUploadFileProbe extends upload_file
{
    public function exposedUploadSizeLimit(): string
    {
        return $this->uploadSizeLimit();
    }
}

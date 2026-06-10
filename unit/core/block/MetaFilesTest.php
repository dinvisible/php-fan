<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;


class MetaFilesTest extends TestCase
{
    public static function metaFileProvider(): array
    {
        return [
            'admin data' => ['core/block/admin/data.meta.php'],
            'admin data form' => ['core/block/admin/data_form.meta.php'],
            'admin data info' => ['core/block/admin/data_info.meta.php'],
            'admin data table' => ['core/block/admin/data_table.meta.php'],
            'admin form pattern' => ['core/block/admin/form_pattern.meta.php'],
            'admin index' => ['core/block/admin/index.meta.php'],
            'admin root' => ['core/block/admin/root.meta.php'],
            'admin structure' => ['core/block/admin/structure.meta.php'],
            'common html pager' => ['core/block/common/html_pager.meta.php'],
            'root html' => ['core/block/root/html.meta.php'],
        ];
    }

    #[DataProvider('metaFileProvider')]
    /**
     * @param string $file File path or file descriptor handled by the operation.
     */
    public function testMetaFileReturnsOwnConfigurationArray(string $file): void
    {
        $meta = require dirname(__DIR__, 3) . '/' . $file;

        $this->assertIsArray($meta, $file);
        $this->assertArrayHasKey('own', $meta, $file);
        $this->assertIsArray($meta['own'], $file);
        $this->assertNotEmpty($meta['own'], $file);
    }

    public function testAdminDataMetaListsEditableInputTypes(): void
    {
        $meta = require dirname(__DIR__, 3) . '/core/block/admin/data.meta.php';

        $types = $meta['own']['editableTypes'];
        $this->assertSame(1, $types['text']);
        $this->assertSame(1, $types['password']);
        $this->assertSame(1, $types['select_dependent_last_ml']);
        $this->assertTrue($meta['own']['notUseTemplate']);
    }

    public function testAdminIndexMetaKeepsStaticConfigurationOnly(): void
    {
        $meta = require dirname(__DIR__, 3) . '/core/block/admin/index.meta.php';

        $this->assertSame('Admin System', $meta['own']['title']);
        $this->assertArrayNotHasKey('embedJS', $meta['own']);
        $this->assertSame('~/ctrl/main_ctrl.js', $meta['own']['externalJS']['head']['m03']);
    }

}

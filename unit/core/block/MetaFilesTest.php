<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/tools/ai_map.php';


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

    public function testAiMapMetaInventoryIncludesKnownCoreMetaFiles(): void
    {
        $root = dirname(__DIR__, 3);
        $expectedFiles = array_map(static fn(array $row): string => $row[0], self::metaFileProvider());
        sort($expectedFiles);
        $actualFiles = php_fan_ai_meta_files($root);

        $this->assertSame($expectedFiles, array_values(array_intersect($actualFiles, $expectedFiles)));
    }

    public function testMetaFilesMatchAiSchemaContract(): void
    {
        $root = dirname(__DIR__, 3);
        $schemaFile = $root . '/.ai/meta.schema.json';
        $schema = json_decode((string)file_get_contents($schemaFile), true);

        $this->assertIsArray($schema);
        $this->assertSame('PHP-FAN block meta file', $schema['title'] ?? null);
        $this->assertArrayHasKey('$defs', $schema);

        $templateFiles = php_fan_ai_template_files($root);
        $metadata = php_fan_ai_metadata_map($root, php_fan_ai_meta_files($root), $templateFiles);

        foreach (php_fan_ai_meta_files($root) as $file) {
            $data = php_fan_ai_meta_file_data($root, $file);
            $this->assertNotEmpty($data, $file);

            foreach ($data as $sectionName => $section) {
                $this->assertIsString($sectionName, $file);
                $this->assertIsArray($section, $file . ':' . $sectionName);

                $this->assertMetaSectionReferencesExistingFiles($root, $file, $sectionName, $section);
            }

            $pairedTemplate = $metadata['meta']['files'][$file]['paired_template'];
            if ($pairedTemplate !== null) {
                $this->assertContains($pairedTemplate, $templateFiles);
                $this->assertFileExists($root . '/' . $pairedTemplate);
            }
        }
    }

    private function assertMetaSectionReferencesExistingFiles(string $root, string $file, string $sectionName, array $section): void
    {
        if (isset($section['default_tpl'])) {
            $this->assertIsString($section['default_tpl'], $file . ':' . $sectionName . ':default_tpl');
            $templatePath = str_starts_with($section['default_tpl'], '/')
                ? $section['default_tpl']
                : $root . '/' . ltrim($section['default_tpl'], '/');
            $this->assertFileExists($templatePath, $file . ':' . $sectionName . ':default_tpl');
        }

        if (isset($section['embeddedBlocks'])) {
            $this->assertIsArray($section['embeddedBlocks'], $file . ':' . $sectionName . ':embeddedBlocks');
            foreach ($section['embeddedBlocks'] as $key => $className) {
                $this->assertIsString($key, $file . ':' . $sectionName . ':embeddedBlocks');
                $this->assertIsString($className, $file . ':' . $sectionName . ':embeddedBlocks.' . $key);
                $this->assertNotSame('', $className, $file . ':' . $sectionName . ':embeddedBlocks.' . $key);
            }
        }
    }
}

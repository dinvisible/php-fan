<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;

if (!function_exists('role')) {
    function role($condition): bool
    {
        return $condition === 'admin';
    }
}

class MetaFilesTest extends \PHPUnit\Framework\TestCase
{
    public static function metaFileProvider(): array
    {
        return [
            'admin data' => ['_core/block/admin/data.meta.php'],
            'admin data form' => ['_core/block/admin/data_form.meta.php'],
            'admin data info' => ['_core/block/admin/data_info.meta.php'],
            'admin data table' => ['_core/block/admin/data_table.meta.php'],
            'admin form pattern' => ['_core/block/admin/form_pattern.meta.php'],
            'admin index' => ['_core/block/admin/index.meta.php'],
            'admin root' => ['_core/block/admin/root.meta.php'],
            'admin structure' => ['_core/block/admin/structure.meta.php'],
            'common html pager' => ['_core/block/common/html_pager.meta.php'],
            'common html pager quantifier' => ['_core/block/common/html_pager_quantifier.meta.php'],
            'form parser' => ['_core/block/form/parser.meta.php'],
            'form usual' => ['_core/block/form/usual.meta.php'],
            'root html' => ['_core/block/root/html.meta.php'],
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
        $meta = require dirname(__DIR__, 3) . '/_core/block/admin/data.meta.php';

        $types = $meta['own']['editableTypes'];
        $this->assertSame(1, $types['text']);
        $this->assertSame(1, $types['password']);
        $this->assertSame(1, $types['select_dependent_last_ml']);
        $this->assertTrue($meta['own']['notUseTemplate']);
    }

    public function testAdminIndexMetaBuildsRuntimeEmbedScriptFromRole(): void
    {
        $meta = require dirname(__DIR__, 3) . '/_core/block/admin/index.meta.php';

        $this->assertSame('Admin System', $meta['own']['title']);
        $this->assertStringContainsString('mainCtrl.init(1,', $meta['own']['embedJS']['head']);
        $this->assertSame('/js/js-wrapper.js', $meta['own']['externalJS']['head']['m01']);
    }

    public function testFormUsualMetaContainsDefaultDesignAndValidatorSettings(): void
    {
        $meta = require dirname(__DIR__, 3) . '/_core/block/form/usual.meta.php';

        $form = $meta['own']['form'];
        $this->assertSame('POST', $form['action_method']);
        $this->assertSame('form_validation', $form['js_validator']);
        $this->assertSame('submit_1', $form['default_type']['button']);
        $this->assertStringContainsString('<input type="text"', $form['design']['input']['text']);
    }

    public function testPagerQuantifierMetaDescribesGetFormField(): void
    {
        $meta = require dirname(__DIR__, 3) . '/_core/block/common/html_pager_quantifier.meta.php';

        $form = $meta['own']['form'];
        $this->assertSame('GET', $form['action_method']);
        $this->assertSame('G', $form['request_type']);
        $this->assertFalse($form['redirect_required']);
        $this->assertSame('select', $form['fields']['pager_quantifier']['input_type']);
    }
}

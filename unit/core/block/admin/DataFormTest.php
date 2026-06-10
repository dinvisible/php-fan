<?php

declare(strict_types=1);

use fan\core\base\model\row;
use fan\core\block\admin\data_form;
use FanTest\core\SourceFileContractTestCase;

class BlockAdminDataFormTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/block/admin/data_form.php';

    public function testGetFieldLabelUsesFormRowsLabel(): void
    {
        $block = new BlockAdminDataFormProbe([
            'form_struct' => [
                'rows' => [
                    ['field' => 'title', 'label' => 'Title'],
                ],
            ],
        ]);

        $this->assertSame('Title', $block->getFieldLabel('title'));
        $this->assertSame('missing', $block->getFieldLabel('missing'));
    }

    public function testGetContentDataReturnsOnlySqlBackedFormFields(): void
    {
        $block = new BlockAdminDataFormProbe([
            'form_struct' => [
                'rows' => [
                    ['field' => 'title'],
                    ['field' => 'virtual', 'notSQL' => true],
                    ['field' => 'status'],
                ],
            ],
        ], new BlockAdminDataFormRowDouble(['title' => 'Hello', 'status' => 'active']));

        $this->assertSame([
            'title' => 'Hello',
            'status' => 'active',
        ], $block->getContentData());
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringContainsString('$this->arrayAdducer()', $source);
    }
}

final class BlockAdminDataFormProbe extends data_form
{
    public function __construct(private array $metaData = [], ?BlockAdminDataFormRowDouble $row = null)
    {
        $this->row = $row;
        $this->setBlockDependencies([
            'arrayAdducer' => static fn(mixed $value): array => empty($value) ? [] : (is_array($value) ? $value : [$value]),
            'recursiveMerger' => static fn(mixed ...$values): array => array_replace_recursive(
                ...array_map(static fn(mixed $value): array => is_array($value) ? $value : [], $values)
            ),
        ]);
    }

    public function getMeta(string|array|null $key = null, mixed $default = null, bool $convToArray = false): mixed
    {
        $value = $this->metaData;
        foreach ((array)$key as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }
        return $key === null ? $this->metaData : $value;
    }

    public function getCurrentRow(bool $cacheEnable): row
    {
        return $this->row instanceof row ? $this->row : new BlockAdminDataFormRowDouble();
    }
}

final class BlockAdminDataFormRowDouble extends row
{
    public function __construct(private array $fields = [], private bool $loaded = true)
    {
    }

    public function checkIsLoad(): bool
    {
        return $this->loaded;
    }

    public function getFields(mixed $keys = null, bool $allExists = true): array
    {
        return $this->fields;
    }
}

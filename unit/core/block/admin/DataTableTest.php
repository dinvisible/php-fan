<?php

declare(strict_types=1);

use fan\core\block\admin\data_table;
use FanTest\core\SourceFileContractTestCase;

class BlockAdminDataTableTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/block/admin/data_table.php';

    public function testValidateDataCollectsUniqueFieldErrorsAcrossEditAndInsert(): void
    {
        $block = new BlockAdminDataTableProbe(errors: [
            'edit' => ['name' => 'Name is invalid'],
            'ins' => ['name' => 'Name is invalid', 'email' => 'Email is invalid'],
        ]);
        $edit = [5 => ['name' => 'bad']];
        $insert = ['new' => ['name' => 'bad', 'email' => 'bad']];

        $this->assertFalse($block->validateData($edit, $insert));
        $this->assertSame(['Name is invalid\nEmail is invalid'], $block->exposedErrorMessages());
    }

    public function testGetFieldLabelUsesColumnHead(): void
    {
        $block = new BlockAdminDataTableProbe(metaData: [
            'table_struct' => [
                'columns' => [
                    ['field' => 'name', 'head' => 'Name'],
                ],
            ],
        ]);

        $this->assertSame('Name', $block->getFieldLabel('name'));
        $this->assertSame('missing', $block->getFieldLabel('missing'));
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringContainsString('$this->arrayAdducer()', $source);
    }
}

final class BlockAdminDataTableProbe extends data_table
{
    public function __construct(private array $metaData = [], private array $errors = [])
    {
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

    public function doValidate(array &$data, string $type, int|float|string|null $id = null): array
    {
        return $this->errors[$type] ?? [];
    }

    public function exposedErrorMessages(): array
    {
        return $this->errorMsg;
    }
}

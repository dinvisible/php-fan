<?php

declare(strict_types=1);

use fan\core\block\admin\data;
use FanTest\_core\SourceFileContractTestCase;

class BlockAdminDataTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/block/admin/data.php';

    public function testDoValidateTrimsDataAndReportsConfiguredRuleErrors(): void
    {
        $block = new BlockAdminDataProbe([
            'validateRequiredMsg' => '{FIELD_LABEL} required',
            'validation' => [
                'age' => [
                    'validate_rules' => [
                        [
                            'rule_name' => 'is_int',
                            'rule_data' => ['min_value' => 10],
                            'error_msg' => '{FIELD_LABEL} must be integer',
                        ],
                    ],
                ],
                'email' => [
                    'is_required' => true,
                ],
            ],
        ], ['age' => 'Age', 'email' => 'Email']);
        $data = ['age' => ' 7 ', 'email' => ''];

        $this->assertSame([
            'age' => 'Age must be integer',
            'email' => 'Email required',
        ], $block->doValidate($data, 'ins'));
        $this->assertSame('7', $data['age']);
    }

    public function testScalarValidationRulesCoverNumericEmailAndRegexpCases(): void
    {
        $block = new BlockAdminDataProbe();

        $this->assertTrue($block->rule('is_int', '12', ['min_value' => 10, 'max_value' => 20]));
        $this->assertFalse($block->rule('is_int', '9', ['min_value' => 10]));
        $this->assertTrue($block->rule('is_float', '1,25', ['min_value' => 1.2, 'max_value' => 1.3]));
        $this->assertTrue($block->rule('is_email', 'dev@example.com'));
        $this->assertFalse($block->rule('is_email', 'not-email'));
        $this->assertTrue($block->rule('match_regexp', 'ABC-12', ['regexp' => '/^[A-Z]+-\\d+$/']));
    }

    public function testAddParamUsesInjectedEntityServiceForPrimaryKey(): void
    {
        $entityService = new BlockAdminDataEntityServiceDouble('id_product');
        $block = new BlockAdminDataProbe([
            'addParam' => ['mode' => 'edit'],
            'entity' => 'product',
        ], entityService: $entityService);

        $this->assertSame([
            'mode' => 'edit',
            'id_name' => 'id_product',
        ], $block->getAddParam());
        $this->assertSame(['product'], $entityService->getCalls);
    }

    public function testDateValidationUsesInjectedDateService(): void
    {
        $dateFactory = new BlockAdminDataDateFactoryDouble([
            '01.02.2026|' => '2026-02-01',
            '03.02.2026|euro' => '2026-02-03',
            '02.02.2026|euro' => '2026-02-02',
        ]);
        $block = new BlockAdminDataProbe(dateFactory: $dateFactory);
        $block->fieldValue = ['start' => '02.02.2026'];

        $this->assertTrue($block->rule('is_date', '01.02.2026', [
            'min_value' => '2026-01-01',
            'max_value' => '2026-12-31',
        ]));
        $this->assertTrue($block->rule('greater_than', '03.02.2026', [
            'compare_field' => 'start',
            'data_type' => 'DATE',
        ]));
        $this->assertSame([
            ['01.02.2026'],
            ['03.02.2026', 'euro'],
            ['02.02.2026', 'euro'],
        ], $dateFactory->calls);
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringNotContainsString('array_merge_recursive_alt(', $source);
        $this->assertStringContainsString('$this->arrayAdducer()', $source);
        $this->assertStringContainsString('$this->recursiveMerger()', $source);
    }
}

final class BlockAdminDataProbe extends data
{
    public array $fieldValue = [];

    public function __construct(
        private array $metaData = [],
        private array $labels = [],
        private ?BlockAdminDataEntityServiceDouble $entityService = null,
        private ?BlockAdminDataDateFactoryDouble $dateFactory = null,
    ) {
        $this->setBlockDependencies([
            'arrayAdducer' => static fn(mixed $value): array => empty($value) ? [] : (is_array($value) ? $value : [$value]),
            'recursiveMerger' => static fn(mixed ...$values): array => array_replace_recursive(
                ...array_map(static fn(mixed $value): array => is_array($value) ? $value : [], $values)
            ),
        ]);
    }

    public function getMeta(string|array|null $key = null, mixed $default = null, bool $convToArray = false): mixed
    {
        if ($key === null) {
            return $this->metaData;
        }
        $path = (array)$key;
        $value = $this->metaData;
        foreach ($path as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }
        return $value;
    }

    public function getFieldLabel(mixed $name): mixed
    {
        return $this->labels[$name] ?? $name;
    }

    public function rule(string $name, mixed $value, array $data = []): bool
    {
        return $this->{'rule_' . $name}($value, $data);
    }

    protected function entityService(mixed ...$arguments): object
    {
        return $this->entityService ?? new BlockAdminDataEntityServiceDouble('id');
    }

    protected function dateService(mixed ...$arguments): object
    {
        return ($this->dateFactory ??= new BlockAdminDataDateFactoryDouble())(...$arguments);
    }
}

final class BlockAdminDataEntityServiceDouble
{
    public array $getCalls = [];

    public function __construct(private string $primaryKey)
    {
    }

    public function get(string $entity): BlockAdminDataEntityDouble
    {
        $this->getCalls[] = $entity;

        return new BlockAdminDataEntityDouble($this->primaryKey);
    }
}

final class BlockAdminDataEntityDouble
{
    public function __construct(private string $primaryKey)
    {
    }

    public function getDescription(): BlockAdminDataDescriptionDouble
    {
        return new BlockAdminDataDescriptionDouble($this->primaryKey);
    }
}

final class BlockAdminDataDescriptionDouble
{
    public function __construct(private string $primaryKey)
    {
    }

    public function getPrimeryKey(): string
    {
        return $this->primaryKey;
    }
}

final class BlockAdminDataDateFactoryDouble
{
    public array $calls = [];

    public function __construct(private array $mysqlByInput = [])
    {
    }

    public function __invoke(string $value, string $format = ''): BlockAdminDataDateDouble
    {
        $this->calls[] = $format === '' ? [$value] : [$value, $format];

        return new BlockAdminDataDateDouble($this->mysqlByInput[$value . '|' . $format] ?? $value);
    }
}

final class BlockAdminDataDateDouble
{
    public function __construct(private string $mysql)
    {
    }

    public function get(string $format): string
    {
        return $format === 'mysql' ? $this->mysql : '';
    }
}

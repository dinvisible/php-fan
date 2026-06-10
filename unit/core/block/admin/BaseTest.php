<?php

declare(strict_types=1);

use fan\core\block\admin\base;
use FanTest\core\SourceFileContractTestCase;
use fan\core\base\model\rowset;


class BlockAdminBaseTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/block/admin/base.php';

    public function testGetIncludedArrayCanUseInlineDataForFirstLevel(): void
    {
        $block = new BlockAdminBaseProbe();

        $this->assertSame([
            [
                10 => ['val' => 'Root'],
                20 => ['val' => 'Child'],
            ],
            [0 => 10],
            1,
        ], $block->getIncludedArray([
            'data' => [10 => 'Root', 20 => 'Child'],
            'select' => 10,
        ]));
    }

    public function testTemplateVarRoutesJsonAndTextToDedicatedSetters(): void
    {
        $block = new BlockAdminBaseProbe();

        $this->assertSame($block, $block->exposedSetTemplateVar('json', ['ok' => true]));
        $this->assertSame($block, $block->exposedSetTemplateVar('text', 'done'));

        $this->assertSame([['ok' => true]], $block->jsonCalls);
        $this->assertSame(['done'], $block->textCalls);
    }

    public function testHashArrayUsesInjectedEntityService(): void
    {
        $rowset = new BlockAdminBaseRowsetDouble([
            new BlockAdminBaseRowDouble(['id' => 1, 'name' => 'One']),
            new BlockAdminBaseRowDouble(['id' => 2, 'name' => 'Two']),
        ]);
        $entity = new BlockAdminBaseEntityServiceDouble([
            'category' => new BlockAdminBaseEntityDouble($rowset),
        ]);
        $block = new BlockAdminBaseProbe(entityService: $entity);

        $this->assertSame([
            [0 => 'Choose', 1 => 'One', 2 => 'Two'],
            0,
        ], $block->getHashArray([
            'entity' => 'category',
            'key' => 'id',
            'val' => 'name',
        ], [0 => 'Choose']));
    }

    public function testTemplateCodeUsesInjectedTemplateService(): void
    {
        $templateService = new BlockAdminBaseTemplateServiceDouble();
        $block = new BlockAdminBaseProbe(
            templateService: $templateService,
            templatePath: '/tmp/form.tpl',
            metaData: [
                'tplVars' => ['fromMeta' => 'meta'],
                'tpl_parent_class' => 'ParentTemplate',
            ],
        );

        $this->assertSame('rendered-template', $block->exposedGetTemplateCode(['extra' => 'value']));
        $this->assertSame([['/tmp/form.tpl', 'ParentTemplate', $block]], $templateService->getCalls);
        $this->assertSame([
            ['fromMeta', 'meta'],
            ['extra', 'value'],
        ], $templateService->template->assignCalls);
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('array_merge_recursive_alt(', $source);
        $this->assertStringContainsString('$this->recursiveMerger()', $source);
    }
}

final class BlockAdminBaseProbe extends base
{
    public array $jsonCalls = [];

    public array $textCalls = [];

    public function __construct(
        private ?BlockAdminBaseEntityServiceDouble $entityService = null,
        private ?BlockAdminBaseTemplateServiceDouble $templateService = null,
        private string $templatePath = '',
        private array $metaData = [],
    ) {
        $this->setBlockDependencies([
            'arrayAdducer' => static fn(mixed $value): array => empty($value) ? [] : (is_array($value) ? $value : [$value]),
            'recursiveMerger' => static function (mixed ...$values): array {
                $result = [];
                foreach ($values as $value) {
                    if (!is_array($value)) {
                        continue;
                    }
                    $result = array_replace_recursive($result, $value);
                }

                return $result;
            },
        ]);
    }

    public function setJson(mixed $json, bool $merge = true): static
    {
        $this->jsonCalls[] = $json;

        return $this;
    }

    public function setText(string $text, bool $merge = true): static
    {
        $this->textCalls[] = $text;

        return $this;
    }

    public function exposedSetTemplateVar(string $key, mixed $value): static
    {
        return $this->setTemplateVar($key, $value);
    }

    public function exposedGetTemplateCode(array $addVars = []): string
    {
        return $this->getTemplateCode($addVars);
    }

    public function getTemplate(): ?string
    {
        return $this->templatePath ?: null;
    }

    public function getMeta(string|array|null $key = null, mixed $default = null, bool $convToArray = false): mixed
    {
        return is_string($key) && array_key_exists($key, $this->metaData) ? $this->metaData[$key] : $default;
    }

    protected function entityService(mixed ...$arguments): object
    {
        return $this->entityService ?? new BlockAdminBaseEntityServiceDouble([]);
    }

    protected function templateService(mixed ...$arguments): object
    {
        return $this->templateService ?? new BlockAdminBaseTemplateServiceDouble();
    }
}

final class BlockAdminBaseEntityServiceDouble
{
    public function __construct(private array $entities)
    {
    }

    public function get(string $entity): mixed
    {
        return $this->entities[$entity] ?? null;
    }
}

final class BlockAdminBaseEntityDouble
{
    public function __construct(private BlockAdminBaseRowsetDouble $rowset)
    {
    }

    public function getRowsetByParam(mixed $param, int $qtt, int $offset, string $order): BlockAdminBaseRowsetDouble
    {
        return $this->rowset;
    }
}

final class BlockAdminBaseRowsetDouble extends rowset
{
    public function __construct(private array $rows)
    {
        $this->data = $rows;
    }
}

final class BlockAdminBaseRowDouble
{
    public function __construct(private array $data)
    {
    }

    public function get(mixed $key): mixed
    {
        return $this->data[$key] ?? null;
    }
}

final class BlockAdminBaseTemplateServiceDouble
{
    public BlockAdminBaseTemplateDouble $template;

    public array $getCalls = [];

    public function __construct()
    {
        $this->template = new BlockAdminBaseTemplateDouble();
    }

    public function get(string $template, ?string $parentClass, object $block): BlockAdminBaseTemplateDouble
    {
        $this->getCalls[] = [$template, $parentClass, $block];

        return $this->template;
    }
}

final class BlockAdminBaseTemplateDouble
{
    public array $assignCalls = [];

    public function assign(string $key, mixed $value): void
    {
        $this->assignCalls[] = [$key, $value];
    }

    public function fetch(): string
    {
        return 'rendered-template';
    }
}

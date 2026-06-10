<?php

declare(strict_types=1);

use fan\core\block\admin\select_dependent;
use FanTest\core\SourceFileContractTestCase;

class BlockAdminSelectDependentTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/block/admin/select_dependent.php';

    protected function tearDown(): void
    {
        unset($GLOBALS['block_admin_select_dependent_entities']);
    }

    public function testLoadNextListBuildsHashFromConfiguredEntityLevel(): void
    {
        $GLOBALS['block_admin_select_dependent_entities'] = [
            'category' => new BlockAdminSelectDependentEntityDouble([
                1 => 'One',
                2 => 'Two',
            ]),
        ];
        $block = new BlockAdminSelectDependentProbe([
            'level_data' => [
                2 => [
                    'entity' => 'category',
                    'param_key' => 'parent_id',
                    'key' => 'id',
                    'val' => 'name',
                ],
            ],
        ]);

        $this->assertSame([
            'hash' => [1 => 'One', 2 => 'Two'],
            'level' => 2,
            'cval' => 7,
        ], $block->do_load_next_list(['level' => 2, 'cval' => 7]));
        $this->assertSame([['parent_id' => 7]], $GLOBALS['block_admin_select_dependent_entities']['category']->params);
    }

    public function testInitUsesInjectedServicesAndBuildsResponse(): void
    {
        $GLOBALS['block_admin_select_dependent_entities'] = [
            'category' => new BlockAdminSelectDependentEntityDouble([
                5 => 'Five',
            ]),
        ];
        $role = new BlockAdminSelectDependentRoleDouble();
        $block = new BlockAdminSelectDependentProbe([
            'login_timeout' => 300,
            'level_data' => [
                3 => [
                    'entity' => 'category',
                    'param_key' => 'parent_id',
                    'key' => 'id',
                    'val' => 'name',
                ],
            ],
        ], $role, [
            'op' => 'load_next_list',
            'data' => [
                'level' => 3,
                'cval' => 9,
            ],
        ]);

        $block->init();

        $this->assertSame([['admin', 300]], $role->sessionRoles);
        $this->assertSame([
            'op' => 'load_next_list',
            'data' => [
                'hash' => [5 => 'Five'],
                'level' => 3,
                'cval' => 9,
            ],
        ], $block->jsonPayload);
        $this->assertSame('ok', $block->textPayload);
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $this->assertStringNotContainsString('containerService(', $this->sourceCode());
    }
}

final class BlockAdminSelectDependentProbe extends select_dependent
{
    public array $jsonPayload = [];

    public string $textPayload = '';

    public function __construct(
        private array $metaData = [],
        private ?BlockAdminSelectDependentRoleDouble $role = null,
        private array $dataPayload = [],
    ) {
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

    public function getData(): array
    {
        return $this->dataPayload;
    }

    public function setJson(mixed $json, bool $merge = true): static
    {
        $this->jsonPayload = (array)$json;

        return $this;
    }

    public function setText(string $text, bool $merge = true): static
    {
        $this->textPayload = $text;

        return $this;
    }

    protected function roleService(): object
    {
        return $this->role ??= new BlockAdminSelectDependentRoleDouble();
    }

    protected function entityService(mixed ...$arguments): object
    {
        return new BlockAdminSelectDependentEntityServiceDouble($GLOBALS['block_admin_select_dependent_entities'] ?? []);
    }
}

final class BlockAdminSelectDependentRoleDouble
{
    public array $sessionRoles = [];

    public function setSessionRoles(string $role, mixed $timeout): void
    {
        $this->sessionRoles[] = [$role, $timeout];
    }
}

final class BlockAdminSelectDependentEntityServiceDouble
{
    public function __construct(private array $entities)
    {
    }

    public function get(string $name): mixed
    {
        return $this->entities[$name] ?? null;
    }
}

final class BlockAdminSelectDependentEntityDouble
{
    public array $params = [];

    public function __construct(private array $hash)
    {
    }

    public function getRowsetByParam(array $param): BlockAdminSelectDependentRowsetDouble
    {
        $this->params[] = $param;

        return new BlockAdminSelectDependentRowsetDouble($this->hash);
    }
}

final class BlockAdminSelectDependentRowsetDouble
{
    public function __construct(private array $hash)
    {
    }

    public function getArrayHash(mixed $keyField, mixed $valField): array
    {
        return $this->hash;
    }
}

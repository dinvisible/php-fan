<?php

declare(strict_types=1);

use fan\core\block\common\html_nav_db;
use FanTest\core\SourceFileContractTestCase;

class BlockCommonHtmlNavDbTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/block/common/html_nav_db.php';

    public function testGetNavBuildsTreeFromEntityRowsAndFiltersRoles(): void
    {
        $entity = new BlockCommonHtmlNavDbEntityDouble([
            new BlockCommonHtmlNavDbRowDouble(1, [
                'order_key' => 10,
                'condition_key' => 'root',
                'target' => 'self',
                'menu_type' => 'local',
                '__url_value' => '~/root',
                '__foreign_url' => '',
                '__protocol' => '',
                '__menu_name' => 'Root',
                'menu_key' => 'root',
                'id_menu_element_parent' => null,
                'id_menu_element' => 1,
            ]),
            new BlockCommonHtmlNavDbRowDouble(2, [
                'order_key' => 20,
                'condition_key' => 'child',
                'target' => 'blank',
                'menu_type' => 'foreign',
                '__url_value' => '',
                '__foreign_url' => 'https://example.test',
                '__protocol' => '',
                '__menu_name' => 'Child',
                'menu_key' => 'child',
                'id_menu_element_parent' => 1,
                'id_menu_element' => 2,
            ]),
        ]);
        $block = new BlockCommonHtmlNavDbProbe($entity);

        $this->assertSame([
            1 => [
                'order_key' => 10,
                'condition_key' => 'root',
                'target' => null,
                'url_value' => 'local:~/root:',
                'menu_name' => 'Root',
                'current' => true,
                'children' => [
                    [
                        'order_key' => 20,
                        'condition_key' => 'child',
                        'target' => '_blank',
                        'url_value' => 'foreign:https://example.test:',
                        'menu_name' => 'Child',
                        'current' => false,
                        'children' => [],
                        'parent' => 1,
                    ],
                ],
            ],
        ], $block->nav());
        $this->assertSame($entity->rows[0], $block->navRow(1));
    }

    public function testGetNavNameReturnsLoadedMenuGroupNameOrFalse(): void
    {
        $entity = new BlockCommonHtmlNavDbEntityDouble([], new BlockCommonHtmlNavDbMenuGroupDouble(true, 'Main menu'));
        $block = new BlockCommonHtmlNavDbProbe($entity);

        $this->assertSame('Main menu', $block->navName('primary'));

        $entity->menuGroup = new BlockCommonHtmlNavDbMenuGroupDouble(false, 'Ignored');
        $this->assertFalse($block->navName('primary'));
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $this->assertStringNotContainsString('containerService(', $this->sourceCode());
    }
}

final class BlockCommonHtmlNavDbProbe extends html_nav_db
{
    public function __construct(private BlockCommonHtmlNavDbEntityDouble $entity)
    {
    }

    public function getMenuURL(string $value, string $type, string $protocol): string
    {
        return $type . ':' . $value . ':' . $protocol;
    }

    public function checkCurrentElement(string $key): bool
    {
        return $key === 'root';
    }

    public function nav(mixed $groupKey = null): array
    {
        return $this->_getNav($groupKey);
    }

    public function navName(string $key): string|false
    {
        return $this->_getNavName($key);
    }

    public function navRow(int|float $id): ?object
    {
        return $this->_getNavRow($id);
    }

    protected function roleService(): object
    {
        return new BlockCommonHtmlNavDbRoleDouble();
    }

    protected function entityService(mixed ...$arguments): object
    {
        return $this->entity;
    }
}

final class BlockCommonHtmlNavDbRoleDouble
{
    public function check(mixed $condition): bool
    {
        return $condition !== 'deny';
    }
}

final class BlockCommonHtmlNavDbEntityDouble
{
    public function __construct(
        public array $rows = [],
        public BlockCommonHtmlNavDbMenuGroupDouble $menuGroup = new BlockCommonHtmlNavDbMenuGroupDouble(false, ''),
    ) {
    }

    public function getMenuElement(mixed $groupKey): array
    {
        return $this->rows;
    }

    public function getMenuGroup(string $key): BlockCommonHtmlNavDbMenuGroupDouble
    {
        return $this->menuGroup;
    }
}

final class BlockCommonHtmlNavDbRowDouble
{
    public function __construct(private int $id, private array $fields, private string $role = 'allow')
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function get___url_role(): string
    {
        return $this->role;
    }

    public function getFields(): array
    {
        return $this->fields;
    }
}

final class BlockCommonHtmlNavDbMenuGroupDouble
{
    public function __construct(private bool $loaded, public string $group_name)
    {
    }

    public function checkIsLoad(): bool
    {
        return $this->loaded;
    }
}

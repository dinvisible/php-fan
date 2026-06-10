<?php

declare(strict_types=1);

use fan\core\block\admin\index;
use FanTest\core\SourceFileContractTestCase;
use fan\core\block\base;


class BlockAdminIndexTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/block/admin/index.php';

    public function testInitRequiredDisablesTabCache(): void
    {
        $block = (new ReflectionClass(index::class))->newInstanceWithoutConstructor();
        $tab = new BlockAdminIndexTabDouble();
        (new ReflectionProperty(base::class, 'tab'))->setValue($block, $tab);

        $block->initRequired();

        $this->assertSame(1, $tab->disableCacheCalls);
    }

    public function testInitUsesInjectedRoleServiceForAdminFlag(): void
    {
        $root = new BlockAdminIndexRootDouble();
        $tab = new BlockAdminIndexTabDouble();
        $block = new BlockAdminIndexProbe($tab, $root, false);

        $block->init();

        $this->assertSame(1, $tab->disableCacheCalls);
        $this->assertSame([
            'mainCtrl.init(0, \'/admin\', _wrapper);',
        ], $root->scripts);
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $this->assertStringNotContainsString('containerService(', $this->sourceCode());
    }
}

final class BlockAdminIndexProbe extends index
{
    public function __construct(
        object $tab,
        private BlockAdminIndexRootDouble $root,
        private bool $isAdmin,
    ) {
        $this->tab = $tab;
    }

    protected function roleService(): object
    {
        return new BlockAdminIndexRoleDouble($this->isAdmin);
    }

    protected function _getBlock(string $blockName, bool $allowException = true): ?object
    {
        return $blockName === 'root' ? $this->root : null;
    }
}

final class BlockAdminIndexTabDouble
{
    public int $disableCacheCalls = 0;

    public function disableCache(): void
    {
        $this->disableCacheCalls++;
    }
}

final class BlockAdminIndexRootDouble
{
    public array $scripts = [];

    public function setEmbedJs(string $script): void
    {
        $this->scripts[] = $script;
    }
}

final class BlockAdminIndexRoleDouble
{
    public function __construct(private bool $isAdmin)
    {
    }

    public function check(string $role): bool
    {
        return $role === 'admin' && $this->isAdmin;
    }
}

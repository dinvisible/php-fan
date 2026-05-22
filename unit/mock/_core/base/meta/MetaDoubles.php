<?php

declare(strict_types=1);

namespace {
    require_once __DIR__ . '/../DataFunctions.php';
    require_once __DIR__ . '/../../../../../_core/base/data.php';
    require_once __DIR__ . '/../../../../../_core/base/meta/delayed.php';
    require_once __DIR__ . '/../../../../../_core/base/meta/row.php';
    require_once __DIR__ . '/../../../../../_core/base/meta/maker.php';
}

namespace fan\project\base\meta {
    if (!class_exists(__NAMESPACE__ . '\\delayed', false)) {
        class delayed extends \fan\core\base\meta\delayed
        {
        }
    }

    if (!class_exists(__NAMESPACE__ . '\\row', false)) {
        class row extends \fan\core\base\meta\row
        {
        }
    }
}

namespace FanTest\_core\base\meta {
    class TestMetaTab
    {
        public array $mainMeta = [];

        public function getBlocksMetaByMain($blockName): array
        {
            return $this->mainMeta[$blockName] ?? [];
        }
    }

    class TestMetaBlock
    {
        public TestMetaTab $tab;
        public string $blockName;
        public mixed $forwardedValue = null;

        public function __construct(string $blockName = 'content')
        {
            $this->blockName = $blockName;
            $this->tab = new TestMetaTab();
        }

        public function getBlockName(): string
        {
            return $this->blockName;
        }

        public function getTab(): TestMetaTab
        {
            return $this->tab;
        }

        public function buildValue($left, $right = null): mixed
        {
            return $right === null ? $left : $left . ':' . $right;
        }
    }

    class TestMetaMaker extends \fan\core\base\meta\maker
    {
        public function __construct(?TestMetaBlock $block = null)
        {
            $this->block = $block ?? new TestMetaBlock();
            $this->blockName = $this->block->getBlockName();
        }

        public function replaceSource(string $type, array $data): static
        {
            return $this->_setSource($type, $data);
        }

        public function mergeRow(\fan\core\base\meta\row $row, array $data, $rewriteExisting = true): \fan\core\base\meta\row
        {
            return $row->mergeData($data, $rewriteExisting);
        }

        public function exposeMakeActiveMeta(string $method, $arguments = [], string|object|null $obj = null, bool $delayed = true): mixed
        {
            return $this->_makeActiveMeta($method, $arguments, $obj, $delayed);
        }

        public function exposeLoadBlockSource(string $class, string $path): array
        {
            return $this->_loadBlockSource($class, $path);
        }
    }
}

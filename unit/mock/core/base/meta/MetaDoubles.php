<?php

declare(strict_types=1);

namespace {
    require_once __DIR__ . '/../DataFunctions.php';
    require_once __DIR__ . '/../../../../../core/base/data.php';
    require_once __DIR__ . '/../../../../../core/base/meta/delayed.php';
    require_once __DIR__ . '/../../../../../core/base/meta/row.php';
    require_once __DIR__ . '/../../../../../core/base/meta/maker.php';
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

namespace FanTest\core\base\meta {
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
        public function __construct(
            ?TestMetaBlock $block = null,
            ?\fan\core\base\meta\maker_state $state = null,
            ?callable $phpArrayFileLoader = null,
            ?callable $rowFactory = null,
            ?callable $delayedFactory = null,
            ?callable $blockExceptionFactory = null,
            ?object $fileStorage = null,
            ?callable $recursiveMerger = null,
            ?callable $arrayAdducer = null,
            ?callable $classNameResolver = null
        )
        {
            $this->block = $block ?? new TestMetaBlock();
            $this->blockName = $this->block->getBlockName();
            $this->state = $state ?? new \fan\core\base\meta\maker_state();
            $property = new \ReflectionProperty(\fan\core\base\meta\maker::class, 'phpArrayFileLoader');
            $property->setValue(
                $this,
                $phpArrayFileLoader ?? static fn(string $path, mixed $default = null): mixed => is_readable($path) ? include $path : $default
            );
            $property = new \ReflectionProperty(\fan\core\base\meta\maker::class, 'rowFactory');
            $property->setValue(
                $this,
                $rowFactory ?? static fn(
                    \fan\core\base\meta\maker $maker,
                    array $data,
                    ?\fan\core\base\meta\row $parent = null,
                    int|string|null $keyName = null,
                    ?callable $rowFactory = null
                ): object => new \fan\project\base\meta\row($maker, $data, $parent, $keyName, $rowFactory)
            );
            $property = new \ReflectionProperty(\fan\core\base\meta\maker::class, 'delayedFactory');
            $property->setValue(
                $this,
                $delayedFactory ?? static fn(object|string $object, string $method, mixed $arguments): object =>
                    new \fan\project\base\meta\delayed($object, $method, $arguments)
            );
            $property = new \ReflectionProperty(\fan\core\base\meta\maker::class, 'blockExceptionFactory');
            $property->setValue(
                $this,
                $blockExceptionFactory ?? static fn(
                    string $exceptionClass,
                    object $block,
                    string $message,
                    int $code,
                    ?\Exception $previous = null
                ): \Throwable => new \RuntimeException($message, $code, $previous)
            );
            $property = new \ReflectionProperty(\fan\core\base\meta\maker::class, 'fileStorage');
            $property->setValue(
                $this,
                $fileStorage ?? new class {
                    public function exists(string $path): bool
                    {
                        return is_file($path);
                    }
                }
            );
            $property = new \ReflectionProperty(\fan\core\base\meta\maker::class, 'recursiveMerger');
            $property->setValue(
                $this,
                \Closure::fromCallable($recursiveMerger ?? static fn(mixed ...$values): mixed => \array_merge_recursive_alt(...$values))
            );
            $property = new \ReflectionProperty(\fan\core\base\meta\maker::class, 'arrayAdducer');
            $property->setValue(
                $this,
                \Closure::fromCallable($arrayAdducer ?? static fn(mixed $value): array => \adduceToArray($value))
            );
            $property = new \ReflectionProperty(\fan\core\base\meta\maker::class, 'classNameResolver');
            $property->setValue(
                $this,
                \Closure::fromCallable($classNameResolver ?? static fn(object $object): string => get_class($object))
            );
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

<?php

declare(strict_types=1);

use fan\core\di\meta_maker_factory;
use PHPUnit\Framework\TestCase;
use fan\core\base\meta\maker;
use fan\core\base\meta\maker_state;
use fan\core\base\meta\row;
use fan\core\block\base;


require_once __DIR__ . '/../../../core/factory/delayed_meta_factory.php';
require_once __DIR__ . '/../../../core/base/meta/maker_state.php';
require_once __DIR__ . '/../../../core/base/meta/row.php';
require_once __DIR__ . '/../../../core/base/meta/maker.php';
require_once __DIR__ . '/../../../core/block/base.php';

final class MetaMakerFactoryTest extends TestCase
{
    public function testFactoryCreatesProjectMetaMaker(): void
    {
        if (!class_exists('\fan\project\base\meta\maker', false)) {
            class_alias(maker::class, '\fan\project\base\meta\maker');
        }

        $block = new MetaMakerFactoryBlockDouble();
        $reflector = new MetaMakerFactoryReflectorDouble();
        $state = new maker_state();
        $phpArrayFileLoader = static fn(string $path, mixed $default = null): mixed => $default;
        $recursiveMerger = static fn(mixed ...$values): array => ['recursive' => count($values)];
        $arrayAdducer = static fn(mixed $value): array => is_array($value) ? $value : [$value];
        $classNameResolver = static fn(object $object): string => 'factory-block';
        $rowFactory = static fn(
            maker $maker,
            array $data,
            ?row $parent = null,
            int|string|null $keyName = null,
            ?callable $rowFactory = null
        ): object => new row($maker, $data, $parent, $keyName, $rowFactory);
        $fileStorage = new MetaMakerFactoryFileStorageDouble();

        $maker = (new meta_maker_factory(
            recursiveMerger: $recursiveMerger,
            arrayAdducer: $arrayAdducer,
            classNameResolver: $classNameResolver
        ))(
            $block,
            $reflector,
            $state,
            $phpArrayFileLoader,
            $rowFactory,
            $fileStorage
        );

        $this->assertInstanceOf(maker::class, $maker);
        $this->assertSame($block, $maker->getBlock());
        $this->assertSame([], $maker->getSource()['block']);
        $this->assertSame(['recursive' => 4], $maker->getMixSrcMeta());
    }}

final class MetaMakerFactoryBlockDouble extends base
{
    public function __construct()
    {
    }

    public function getBlockName(): string
    {
        return 'meta-maker-factory';
    }
}

final class MetaMakerFactoryReflectorDouble
{
    public function getParentPaths(object $block): array
    {
        return [
            get_class($block) => __FILE__,
        ];
    }
}

final class MetaMakerFactoryFileStorageDouble
{
    public function exists(string $path): bool
    {
        return false;
    }
}

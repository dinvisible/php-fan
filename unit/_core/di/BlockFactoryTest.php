<?php

declare(strict_types=1);

use fan\core\di\block_factory;
use PHPUnit\Framework\TestCase;
use fan\core\block\base;

final class BlockFactoryTest extends TestCase
{
    public function testFactoryCreatesBlockObjectWithRuntimeArguments(): void
    {
        $tab = new stdClass();
        $dependencies = ['requestFactory' => static fn(): object => new stdClass()];
        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new block_factory(
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            }
        );

        $block = $factory(
            BlockFactoryProbe::class,
            'content',
            $tab,
            null,
            ['title' => 'Hello'],
            true,
            'branch',
            $dependencies
        );

        $this->assertSame(BlockFactoryProbe::class, $delegatedClass);
        $this->assertSame(['content', $tab, null, ['title' => 'Hello'], true, null, $dependencies], $delegatedArguments);
        $this->assertInstanceOf(BlockFactoryProbe::class, $block);
        $this->assertSame('content', $block->blockName);
        $this->assertSame($tab, $block->tabService);
        $this->assertSame(['title' => 'Hello'], $block->meta);
        $this->assertTrue($block->allowMeta);
        $this->assertNull($block->serviceContainer);
        $this->assertSame($dependencies, $block->dependencies);
    }}

final class BlockFactoryProbe
{
    public function __construct(
        public string $blockName,
        public object $tabService,
        public ?base $containerBlock,
        public array $meta,
        public bool $allowMeta,
        public mixed $serviceContainer,
        public array $dependencies
    ) {
    }
}

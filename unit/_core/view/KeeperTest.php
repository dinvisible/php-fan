<?php

declare(strict_types=1);

use fan\core\view\keeper;
use fan\core\view\router;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\base\data;
use fan\core\block\base;


class ViewKeeperTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/view/keeper.php';

    public function testConstructorStoresRouterAndBlockAndDisablesMultiLevel(): void
    {
        $block = new ViewKeeperBlockDouble();
        $router = new ViewKeeperRouterDouble($block);
        $keeper = new keeper($router);

        $this->assertSame($router, $keeper->getRouter());
        $this->assertSame($block, $keeper->getBlock());
        $this->assertFalse($this->boolProperty($keeper, 'multiLevel'));
    }

    public function testSetAndGetWorkWhenCalledByRegisteredRouter(): void
    {
        $router = new ViewKeeperRouterDouble(new ViewKeeperBlockDouble());
        $keeper = new keeper($router);

        $router->writeKeeper($keeper, 'title', 'Hello');

        $this->assertSame('Hello', $keeper->get('title'));
        $this->assertSame(['title' => 'Hello'], $keeper->get());
    }

    public function testFullRewriteReplacesWholeDataSetWhenCalledByBlock(): void
    {
        $block = new ViewKeeperBlockDouble();
        $keeper = new keeper(new ViewKeeperRouterDouble($block));
        $this->setBoolProperty($keeper, 'fullRewrite', true);

        $block->writeKeeper($keeper, null, ['root' => true]);

        $this->assertSame(['root' => true], $keeper->get());
    }

    public function testClearRemovesDataWhenCalledByRegisteredRouter(): void
    {
        $router = new ViewKeeperRouterDouble(new ViewKeeperBlockDouble());
        $keeper = new keeper($router);

        $router->writeKeeper($keeper, 'title', 'Hello');
        $this->assertSame($keeper, $router->clearKeeper($keeper));

        $this->assertSame([], $keeper->get());
    }

    private function boolProperty(keeper $keeper, string $propertyName): bool
    {
        $property = new ReflectionProperty(data::class, $propertyName);
        return $property->getValue($keeper);
    }

    private function setBoolProperty(keeper $keeper, string $propertyName, bool $value): void
    {
        $property = new ReflectionProperty(data::class, $propertyName);
        $property->setValue($keeper, $value);
    }
}

final class ViewKeeperRouterDouble extends router
{
    public function __construct(private base $blockDouble)
    {
    }

    public function getBlock(): base
    {
        return $this->blockDouble;
    }

    public function writeKeeper(keeper $keeper, mixed $key, mixed $value): keeper
    {
        return $keeper->set($key, $value);
    }

    public function clearKeeper(keeper $keeper): keeper
    {
        return $keeper->clear();
    }
}

final class ViewKeeperBlockDouble extends base
{
    public function __construct()
    {
    }

    public function writeKeeper(keeper $keeper, mixed $key, mixed $value): keeper
    {
        return $keeper->set($key, $value);
    }
}
